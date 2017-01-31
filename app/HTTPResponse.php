<?php

namespace App;

use GuzzleHttp\Client;

class HTTPResponse
{
    protected $url;
    protected $response = null;
    public $body = null;

    function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Returns the (cached) GuzzleHttp Response
     *
     */
    public function get()
    {
//        $cached = Redis::hget("response", $this->url);
//        if ($cached)
//          return unserialize($cached);

        try {
            $this->response = $this->client()->get( $this->url, [
                // User-Agent because some sites (e.g. facebook) do not return all headers if the user-agent is missing or Guzzle
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
                ],
                'verify' => false,
                'http_errors' => false,
            ] );

        } catch (\Exception $exception) {
            \Log::critical( $exception );
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
     * This function gets overwritten at the phpunit tests
     * @return Client
     */
    public function client()
    {
        return new Client();
    }

    /**
     * @return int HTTP Status Code
     */
    public function statusCode()
    {
        return $this->get()->getStatusCode();
    }

    /**
     * @return \Illuminate\Support\Collection HTTP headers
     */
    public function headers()
    {
        return collect($this->get()->getHeaders());
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
        return $this->get()->getBody();
    }

}