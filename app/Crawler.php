<?php

namespace App;

use Illuminate\Support\Collection;
use voku\helper\HtmlDomParser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

class Crawler
{
    protected $id;
    protected $mainUrl;
    protected $options;
    protected $guzzleOptions;
    protected $whitelist;
    protected $limit;

    protected $toCrawl;
    protected $crawledUrls;

    // TODO: if header is image or pdf, dont crawl this.
    // TODO: Downnload des Reports in einer HTML Datei
    // TODO: feature sitemap.xml einlesen und auswerten.

    /**
     * Crawler constructor.
     * @param $id
     * @param $mainUrl
     * @param Collection $whitelist
     * @param Collection $options
     * @param $limit
     * @internal param $url
     */
    public function __construct($id, $mainUrl, Collection $whitelist, Collection $options = null, $limit = null)
    {
        $this->id = $id;
        $this->mainUrl = $mainUrl;
        $this->whitelist = $whitelist;
        $this->options = $options;

        if ($limit === null)
            $this->limit = env('LIMIT', 100);
        else
            $this->limit = $limit;

        $this->toCrawl = collect([$mainUrl]);
        $this->crawledUrls = collect();

        $this->guzzleOptions = collect(['http_errors' => true]);
        if ($this->options->contains('ignoreTLS'))
            $this->guzzleOptions->put('verify', false);
        if ($this->options->has('proxy'))
            $this->guzzleOptions->put('proxy', $this->options->get('proxy'));

    }

    public function extractAllLinks()
    {
        while ($link = $this->toCrawl->pop()) {

                $this->crawledUrls = $this->crawledUrls->push($link);

                $extractedLinks = $this->extractLinks($link)->unique();

                // Limit
                if ( $this->crawledUrls->count() > $this->limit)
                    break;

                foreach ($extractedLinks->diff($this->crawledUrls)->diff($this->toCrawl) as $extractedLink) {
                    // Limit
                    if( $this->toCrawl->count() + $this->crawledUrls->count() >= $this->limit )
                        break;

                    $this->toCrawl->push($extractedLink)->unique();
                }

                Redis::hset($this->id, 'amountUrlsToCrawl', $this->toCrawl->count());
                Redis::hset($this->id, 'amountUrls', $this->crawledUrls->count());
                \Log::info('URLs crawled: ' . Redis::hget($this->id, 'amountUrls') . " / " . (Redis::hget($this->id, 'amountUrls') + $this->toCrawl->count()));
            }

        Redis::hset($this->id, "crawledUrls", $this->crawledUrls);
    }

    /**
     * Extract the links on the given $url.
     *
     * @param $link
     * @return Collection
     */
    protected function extractLinks($link)
    {
        $parsedUrls = $this->parseDom($link);
        $optimizedUrls = $this->optimizeUrls($link, $parsedUrls);

        return $optimizedUrls;
    }

    /**
     * Returns the (cached) GuzzleHttp Response
     *
     * @param $url
     * @return CachedResponse
     */
    protected function getHttpResponse($url)
    {
        $cached = Redis::hget("response", $url);
        if ($cached)
            return unserialize($cached);

        $client = new Client([
            'timeout' => 0
        ]);

        $response = $client->get($url, $this->guzzleOptions->toArray());
        $response->url = $url;

        return new CachedResponse($this->id, $url, $response);
    }

    /**
     * Parses the $response with the set $withOptions and returns all links that are found.
     *
     * @param $link
     * @return Collection
     *
     * TODO: Weitere Elemente
     * Orientation: https://github.com/zaproxy/zaproxy/blob/develop/src/org/zaproxy/zap/spider/parser/SpiderHtmlParser.java
     */
    protected function parseDom($link)
    {
        $dom = HtmlDomParser::str_get_html($this->getHttpResponse($link)->getBody());

        $links = collect();

        foreach ($dom->find("a") as $link)
            $links->push($link->href);

        if ($this->options->contains('images'))
            foreach ($dom->find("img") as $link)
                $links->push($link->src);
        if ($this->options->contains('media'))
            foreach ($dom->find("video,audio,source") as $link)
                $links->push($link->src);
        if ($this->options->contains('css'))
            foreach ($dom->find("link") as $link)
                $links->push($link->href);
        if ($this->options->contains('scripts'))
            foreach ($dom->find("script") as $link)
                $links->push($link->src);

        return $links;
    }

    /**
     * Optimizes $urls so they can be used for further HTTP requests.
     *
     * @param $scannedUrl
     * @param Collection $parsedUrls
     * @return Collection with Urls
     */
    protected function optimizeUrls($scannedUrl, Collection $parsedUrls)
    {
        $urls = $parsedUrls
            ->unique()
            // Remove everything behind #
            ->map(function ($value, $key) {
                $pos = strpos($value, '#');
                if ($pos !== false)
                    return substr($value, 0, $pos);
                return $value;
            })
            ->map(function ($value, $key) use ($scannedUrl) {

                $parsed = parse_url($value);
                // TODO: switch-case umschreiben
                switch ($value) {
                    case strncmp($value, 'http', 4) === 0:
                        return $this->unparse_url($parsed, $scannedUrl);
                        break;
                    case strncmp($value, '//', 2) === 0:
                        return $this->unparse_url($parsed, $scannedUrl);
                        break; // parse_url($scannedUrl, PHP_URL_SCHEME)  . $value;
                    case strncmp($value, '/', 1) === 0:
                        return $this->unparse_url($parsed, $scannedUrl);
                        break;
                    case strncmp($value, '../', 3) === 0:
                        return $this->unparse_url($parsed, $scannedUrl);
                        break; //parse_url($scannedUrl, PHP_URL_SCHEME) . '://' . parse_url($scannedUrl, PHP_URL_HOST) . substr($value, 3); break;
                    case strncmp($value, './', 2) === 0:
                        return $this->unparse_url($parsed, $scannedUrl);
                        break;

                    default: {
                        if (strpos($value, ':') === false) // filters mailto:, javascript:, data: etc.
                            return $this->unparse_url($parsed, $scannedUrl); //parse_url($scannedUrl, PHP_URL_SCHEME) . '://' . parse_url($scannedUrl, PHP_URL_HOST) . '/' . $value;
                    }
                }
            })
            // Whitelist-Filter
            ->filter(function ($value, $key) {
                return $this->whitelist->contains(strtolower(parse_url($value, PHP_URL_HOST)));
            });
        return $urls;
    }

    /**
     * Unparses a $parsed_url. If the hostname is missing, e.g by relative URLs, use the hostname of the $scanned_url.
     *
     * @param $parsed_url
     * @param $scanned_url
     * @return string
     */
    function unparse_url($parsed_url, $scanned_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : 'https://';
        $host = isset($parsed_url['host']) ? strtolower($parsed_url['host']) : strtolower(parse_url($scanned_url, PHP_URL_HOST));
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        if (strncmp($path, "/", 1) !== 0) $path = "/" . $path;
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        return "$scheme$user$pass$host$port$path$query";
    }

}