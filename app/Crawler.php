<?php

namespace App;

use Illuminate\Support\Collection;
use voku\helper\HtmlDomParser;
use Illuminate\Support\Facades\Redis;

class Crawler
{
    protected $id;
    protected $mainUrl;
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
     * @internal param $url
     */
    public function __construct($id, $mainUrl, Collection $whitelist, Collection $options = null)
    {
        $this->id = $id;
        $this->mainUrl = $mainUrl;
        $this->whitelist = $whitelist;
        $this->options = $options;

        if (! $options->has('limit'))
            $this->options->put('limit', env('LIMIT', 100));

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
        Redis::hset($this->id, 'status', 'crawling');

        while ($link = $this->toCrawl->pop()) {

            $this->crawledUrls = $this->crawledUrls->push($link);

            $extractedLinks = $this->extractLinks($link)->unique();

            // Limit
            if ( $this->crawledUrls->count() > $this->options->get('limit'))
                break;

            foreach ($extractedLinks->diff($this->crawledUrls)->diff($this->toCrawl) as $extractedLink) {
                // Limit
                if( $this->toCrawl->count() + $this->crawledUrls->count() >= $this->options->get('limit') )
                    break;

                $this->toCrawl->push($extractedLink)->unique();
            }

            Redis::hset($this->id, 'amountUrlsToCrawl', $this->toCrawl->count());
            Redis::hset($this->id, 'amountUrls', $this->crawledUrls->count());

            // Merge the toCrawl list with the crawledUrls if the crawler should only return the links on the $mainUrl
            if ($this->options->contains('doNotCrawl')) {
                $this->crawledUrls = $this->crawledUrls->push($this->toCrawl)->flatten()->unique();
                break;
            }
        }
        Redis::hset($this->id, "crawledUrls", serialize($this->crawledUrls));


        return $this->crawledUrls;
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
     * Parses the $response with the set $withOptions and returns all links that are found.
     *
     * @param $link
     * @return Collection
     *
     * Orientation: https://github.com/zaproxy/zaproxy/blob/develop/src/org/zaproxy/zap/spider/parser/SpiderHtmlParser.java
     */
    protected function parseDom($link)
    {
        $dom = HtmlDomParser::str_get_html((new HTTPResponse($link))->getBody());

        $links = collect();

        if($this->options->has('customJson'))
            foreach (json_decode($this->options->get('customJson')) as $tag => $attribute)
                foreach ($dom->find($tag) as $element)
                    $links->push($element->$attribute);
        else {
            if ($this->options->contains("anchors"))
                foreach ($dom->find("a") as $link)
                    $links->push($link->href);
            if ($this->options->contains('images'))
                foreach ($dom->find("img") as $link)
                    $links->push($link->src);
            if ($this->options->contains('media'))
                foreach ($dom->find("video,audio,source") as $link)
                    $links->push($link->src);
            if ($this->options->contains('links'))
                foreach ($dom->find("link") as $link)
                    $links->push($link->href);
            if ($this->options->contains('scripts'))
                foreach ($dom->find("script") as $link)
                    $links->push($link->src);
            if ($this->options->contains('area'))
                foreach ($dom->find("area") as $link)
                    $links->push($link->href);
            if ($this->options->contains('frames'))
                foreach ($dom->find("iframe,frame") as $link)
                    $links->push($link->src);
        }

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
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : 'http://';
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