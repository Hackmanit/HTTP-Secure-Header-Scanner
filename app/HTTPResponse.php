<?php

namespace App;

use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;

class HTTPResponse
{
    protected $url;
    protected $response = null;

    function __construct($url, Client $client = null)
    {
        $this->url = $url;
        $this->client = $client;
    }

    /**
     * Returns the (cached) GuzzleHttp Response
     *
     */
    public function response()
    {
        if ($this->response === null) {
            if ($this->client === null) {
                /**
                 * The $stack enables caching for the network traffic
                 * BEST THANKS AND WISHES TO @Kevinrob for guzzle-cache-middleware
                 */
                $stack = HandlerStack::create();
                $stack->push(
                    new CacheMiddleware(
                        new PrivateCacheStrategy(
                            new LaravelCacheStorage(
                                Cache::store(env('CACHE_DRIVER', 'file'))
                            )
                        )
                    ),
                    'cache'
                );
                $this->client = new Client( ['handler' => $stack] );
            }

            try {
                $this->response = $this->client->get( $this->url, [
                    // User-Agent because some sites (e.g. facebook) do not return all headers if the user-agent is missing or Guzzle
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
                    ],
                    'verify' => false,
                    'http_errors' => false,
                ] );

            } catch (\Exception $exception) {
                \Log::critical( $this->url . ": " . $exception);
            }
        }
        return $this->response;
    }

    /**
     * @return mixed original URL
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * @return int HTTP Status Code
     */
    public function statusCode()
    {
        return $this->response()->getStatusCode();
    }

    /**
     * @return \Illuminate\Support\Collection HTTP headers
     */
    public function headers()
    {
        return collect($this->response()->getHeaders());
    }

    /**
     * @param $name string header name in lowercase
     * @return array
     */
    public function header($name)
    {
        return $this->headers()->mapWithKeys(function( $value, $key ) {
            return [strtolower($key) => $value];
        })->get(strtolower($name));
    }

    /**
     * @return string HTTP Body
     */
    public function body()
    {
        return $this->response()->getBody()->getContents();
    }

}