<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use voku\helper\HtmlDomParser;
use Illuminate\Support\Facades\Redis;

class Crawler
{
    protected $id;
    protected $mainUrl;
    protected $client;
    protected $options;
    protected $guzzleOptions;
    protected $whitelist;

    protected $toCrawl;
    protected $crawledUrls;

    /**
     * Crawler constructor.
     * @param $id
     * @param $mainUrl
     * @param Collection $whitelist
     * @param Collection $options
     * @param Client $client
     * @internal param $url
     */
    public function __construct($id, $mainUrl, Collection $whitelist = null, Collection $options = null, Client $client = null)
    {
        $this->id = $id;
        $this->mainUrl = $mainUrl;
        $this->whitelist = $whitelist;
        if ($this->whitelist === null) {
            $this->whitelist = collect();
            $this->whitelist->push(strtolower(parse_url($mainUrl, PHP_URL_HOST)));
        }
        $this->options = $options;
        $this->client = $client;

        // If proxy should be used and no testing client is given, manage cache and proxy
        if ($client === null && $options->has('proxy')) {
            $stack = HandlerStack::create();
            $stack->push(
                new CacheMiddleware(
                    new PrivateCacheStrategy(
                        new LaravelCacheStorage(
                            Cache::store('redis')
                        )
                    )
                ),
                'cache'
            );
            $this->client = new Client( ['proxy' => $options->get('proxy') ,'handler' => $stack] );
        }

        if (! $options->has('limit'))
            $this->options->put('limit', env('LIMIT', 100));

        $this->toCrawl = collect([$mainUrl]);
        $this->crawledUrls = collect();
    }

    public function extractAllLinks()
    {
        // Set status to "crawling"
        Redis::hset($this->id, 'status', 'crawling');

        while ($this->toCrawl->count() > 0) {
            $link = $this->toCrawl->pop();
            $this->crawledUrls->push($link);

            Redis::hset($this->id, 'amountUrlsCrawled', $this->crawledUrls->count());
            Redis::hset($this->id, 'amountUrlsToCrawl', $this->toCrawl->count());

            if($this->crawledUrls->count() >= $this->options->get('limit'))
                break;

            $extractedLinks = $this->extractLinks($link)->unique();
            foreach ($extractedLinks as $extractedLink) {
                if ((! $this->toCrawl->contains($extractedLink)) && (! $this->crawledUrls->contains($extractedLink))) {
                    $this->toCrawl->push($extractedLink);
                }
                Redis::hset($this->id, 'amountUrlsCrawled', $this->crawledUrls->count());
                Redis::hset($this->id, 'amountUrlsToCrawl', $this->toCrawl->count());
            }
        }

        return $this->crawledUrls;
    }

    /**
     * Extract the links on the given $url.
     *
     * @param $link
     * @return Collection
     */
    public function extractLinks($link)
    {
        $parsedUrls = $this->parseDom($link);
        $optimizedUrls = $this->optimizeUrls($link, $parsedUrls);
        return $optimizedUrls;
    }

    /**
     * Parses the $response with the set $withOptions and returns all links that are found.
     *
     * @param $link
     * @return Collection
     *
     * Orientation: https://github.com/zaproxy/zaproxy/blob/develop/src/org/zaproxy/zap/spider/parser/SpiderHtmlParser.java
     */
    protected function parseDom($link)
    {
        $response = new HTTPResponse($link, $this->client);
        $links = collect();
        if($response  != null) {

            $dom = HtmlDomParser::str_get_html($response->body());

            if($this->options->has('customElements')) {
                foreach (json_decode( $this->options->get( 'customElements' ) ) as $tag => $attribute)
                    foreach ($dom->find( $tag ) as $element)
                        $links->push( $element->$attribute );
            }

            if ($this->options->contains("anchor"))
                foreach ($dom->find( "a" ) as $link)
                    $links->push( $link->href );
            if ($this->options->contains('image'))
                foreach ($dom->find("img") as $link)
                    $links->push($link->src);
            if ($this->options->contains('media'))
                foreach ($dom->find("video,audio,source") as $link)
                    $links->push($link->src);
            if ($this->options->contains('link'))
                foreach ($dom->find("link") as $link)
                    $links->push($link->href);
            if ($this->options->contains('script'))
                foreach ($dom->find("script") as $link)
                    $links->push($link->src);
            if ($this->options->contains('area'))
                foreach ($dom->find("area") as $link)
                    $links->push($link->href);
            if ($this->options->contains('frame'))
                foreach ($dom->find("iframe,frame") as $link)
                    $links->push($link->src);

            return $links;
        }
        return null;
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

        // Add the $scannedUrl to the list if it does not have any further $urls like .pdf or .jpg etc.
        if($parsedUrls->count() == 0) {
            $this->id;
            $return = collect([$this->unparse_url($scannedUrl, $scannedUrl)]);
            return $return;
        }

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
                if(
                    (strncmp($value, 'http', 4) === 0)  ||  // http(s) / Ports
                    (strncmp($value, '//', 2) === 0)    ||  // all protocols / Ports
                    (strncmp($value, '/', 1) === 0)     ||
                    (strncmp($value, '../', 3) === 0)   ||
                    (strncmp($value, './', 2) === 0)    ||
                    (strpos($value, ':') === false)         // filter mailto:, data:, javascript:
                ) {
                    // Removes leading ./
                    if (strncmp($value, './', 2) === 0)
                        $value = substr($value, 2);

                    $parsed = parse_url($value);
                    return $this->unparse_url($parsed, $scannedUrl);
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
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : strtolower(parse_url($this->mainUrl, PHP_URL_SCHEME)) . "://";
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