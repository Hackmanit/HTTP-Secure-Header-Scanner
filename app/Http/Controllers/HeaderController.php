<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Validator;
use GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Response;

use voku\helper\HtmlDomParser;

use Illuminate\Support\Facades\Redis;
use App\Report;

class HeaderController extends Controller
{

    protected $crawledUrls;


    /**
     * Main function for the routing.
     *
     * @param Request $request
     * @return array
     */
    public function show(Request $request)
    {
        header('Access-Control-Allow-Origin: *');

        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return ["status" => "error", "errors" => $validator->messages()];
        }

        $this->crawledUrls = collect();

        $url = $request->input('url');
        $whiteList = collect(strtolower(parse_url($url, PHP_URL_HOST)));

        // if host is cached, return cache
        if (env('ENABLE_HOST_CACHE', false)) {
            if (Redis::get("report-" . $url) !== null)
                return Redis::get("report-" . $url);
        }

        $this->extractAllLinks($url, collect(['images', 'css', 'scripts']), $whiteList);
        //$reports = $this->generateReports($links);

        dd($this->crawledUrls->all());
        // return report
    }



    protected function extractAllLinks($url, Collection $withOptions, Collection $whitelist, $limit = null) {

        if ($limit === null)
            $limit = env('DEFAULT_LIMIT', 100);

        // Extract links of the scanned url (user request)
        $this->extractLinks($url, $withOptions, $whitelist);

        // Crawl each link and add the crawled optimized links
        foreach ($this->crawledUrls as $crawledUrl) {
            $this->extractLinks($crawledUrl, $withOptions, $whitelist);

            // if limit is reached
            if ($this->crawledUrls->count() >= $limit)
                break;
        }

        return "Finished.";
    }

    /**
     * Extract the links on the given $url.
     *
     * @param $url
     * @param Collection $withOptions
     * @param Collection $whitelist
     * @return bool
     */
    protected function extractLinks($url, Collection $withOptions, Collection $whitelist)
    {
        $parsedUrls = $this->parseDom($this->getHttpResponse($url), $withOptions);
        $optimizedUrls = $this->optimizeUrls($url, $parsedUrls, $whitelist);

        $this->crawledUrls = $this->crawledUrls->push($optimizedUrls)->flatten()->unique();

        return true;
    }

    /**
     * Returns the (cached) GuzzleHttp Response
     *
     * @param $url
     * @return CachedResponse
     */
    protected function getHttpResponse($url)
    {
        $cached = Redis::get("response-" . $url);
        if($cached)
            return unserialize($cached);

        $client = new Client();
        $response = $client->get($url, ['http_errors' => true]);
        $response->url = $url;

        return new CachedResponse($url, $response);
    }

    /**
     * Parses the $response with the set $withOptions and returns all links that are found.
     *
     * @param CachedResponse $response
     * @param Collection $withOptions
     * @return Collection
     *
     * Orientation: https://github.com/zaproxy/zaproxy/blob/develop/src/org/zaproxy/zap/spider/parser/SpiderHtmlParser.java
     */
    protected function parseDom(CachedResponse $response, Collection $withOptions) {
        $dom = HtmlDomParser::str_get_html($response->getBody());

        $links = collect();

        foreach ($dom->find("a") as $link)
            $links->push($link->href);

        if ($withOptions->contains('images'))
            foreach ($dom->find("img") as $link)
                $links->push($link->src);
        if ($withOptions->contains('media'))
            foreach ($dom->find("video,audio,source") as $link)
                $links->push($link->src);
        if ($withOptions->contains('css'))
            foreach ($dom->find("link") as $link)
                $links->push($link->href);
        if ($withOptions->contains('scripts'))
            foreach ($dom->find("script") as $link)
                $links->push($link->src);

        return $links;
    }

    // TODO: Ports
    /**
     * Optimizes $urls so they can be used for further HTTP requests.
     *
     * @param $scannedUrl
     * @param Collection $urls
     * @param Collection $whitelist
     * @return Collection with Urls
     */
    protected function optimizeUrls($scannedUrl, Collection $urls, Collection $whitelist) {
        $urls = $urls
            ->unique()
            ->filter(function ($value, $key) {
                return is_string($value);
            })
            ->map(function ($value, $key) {
                $pos = strpos($value, '#');
                if($pos !== false)
                    return substr($value, 0 , $pos);
                return $value;
            })
            ->map(function ($value, $key) use ($scannedUrl) {
                switch ($value) {
                    case strncmp($value, 'http', 4) === 0:
                        return $value; break;
                    case strncmp($value, '//', 2) === 0:
                        return parse_url($scannedUrl, PHP_URL_SCHEME) . ':' . $value; break;
                    case strncmp($value, '/', 1) === 0:
                        return parse_url($scannedUrl, PHP_URL_SCHEME) . '://' . strtolower(parse_url($scannedUrl, PHP_URL_HOST)) . $value; break;
                    case strncmp($value, '../', 3) === 0:
                        return parse_url($scannedUrl, PHP_URL_SCHEME) . '://' . strtolower(parse_url($scannedUrl, PHP_URL_HOST)) . substr($value, 3); break;
                    case strncmp($value, './', 2) === 0:
                        return parse_url($scannedUrl, PHP_URL_SCHEME) . '://' . strtolower(parse_url($scannedUrl, PHP_URL_HOST)) . substr($value, 2); break;
                    default: {
                        if (strpos($value, ':') === false) // filters mailto:, javascript:, data: etc.
                            return parse_url($scannedUrl, PHP_URL_SCHEME) . '://' . strtolower(parse_url($scannedUrl, PHP_URL_HOST)) . '/' . $value;
                    }

                }
            })
            ->filter(function ($value, $key) use ($whitelist) {
                return $whitelist->contains(strtolower(parse_url($value, PHP_URL_HOST)));
            })
            ->reject(function ($value) {
                return (strpos($value, "../") !== false);
            })
        ;

        return $urls;
    }


    /**
     * Creates the report and handles caching.
     *
     * @param $url
     * @param Response $response
     * @return Report|array
     * @internal param $GuzzleHttp/Psr7/Response $response
     */
    protected function makeReport($url, Response $response)
    {
        $report = new Report($response);
        $report = [
            "status" => "success",
            "nonce" => str_random(),
            "scores" => $report->toJson(),
            "headers" => $response->getHeaders(),
            "url" => $url,
            "date" => date("Y-m-d H:i:s")
        ];

        Redis::set("report-" . $url, serialize($report));
        Redis::expire("report-" . $url, env("HOST_CACHE", 10));


        return $report;
    }

    protected function generateReports(Collection $links) {
        $reports = collect();
        foreach ($links as $link)
            $reports->push($this->makeReport($link, $this->getHttpResponse($link)));

        return $reports;
    }



}

/**
 * This class is used to save the GuzzleHttp Response.
 * The resonse cannot be saved directly because it uses a PHP Stream that could not be saved in the Redis cache.
 *
 * Class CachedResponse
 * @package App\Http\Controllers
 */
class CachedResponse {
    protected $url;
    protected $headers;
    protected $body;

    function __construct($url, Response $response)
    {
        $this->url = $url;
        $this->headers = collect($response->getHeaders());
        $this->body = $response->getBody()->getContents();

        Redis::set("response-" . $url, serialize($this));
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @return Collection
     */
    public function getHeaders() {
        return $this->getHeaders();
    }
}

