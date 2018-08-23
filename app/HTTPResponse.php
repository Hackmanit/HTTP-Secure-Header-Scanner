<?php

namespace App;

use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class HTTPResponse
{
    protected $url;
    protected $response = null;
    protected $hasErrors = false;

    public function __construct($url, Client $client = null)
    {
        $this->url = $url;
        $this->client = $client;

        $this->calculateResponse();
    }

    /**
     * Calculates the HTTPResponse
     *
     * @return void
     */
    protected function calculateResponse() {
        if ($this->response === null) {
            if ($this->client === null) {
                $this->client = new Client();
            }

            try {
                $this->response = $this->client->get($this->url, [
                    // User-Agent because some sites (e.g. facebook) do not return all headers if the user-agent is missing or Guzzle
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
                    ],
                    'verify' => false,
                    'http_errors' => false,
                ]);
            } catch (\Exception $exception) {
                \Log::debug($this->url . ": " . $exception);
                $this->hasErrors = true;
            }
        }
    }

    /**
     * Returns the GuzzleHttp Response
     *
     */
    public function response()
    {
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
        if($this->hasErrors())
            return null;
        return $this->response()->getStatusCode();
    }

    /**
     * @return \Illuminate\Support\Collection HTTP headers
     */
    public function headers()
    {
        if($this->hasErrors())
            return null;

        return collect($this->response()->getHeaders());
    }

    /**
     * @param $name string header name in lowercase
     * @return array
     */
    public function header($name)
    {
        if($this->hasErrors())
            return null;

        return $this->headers()->mapWithKeys(function ($value, $key) {
            return [strtolower($key) => $value];
        })->get(strtolower($name));
    }

    /**
     * @return string HTTP Body
     */
    public function body()
    {
        if($this->hasErrors())
            return null;

        # Fixed empty body
        # See: https://stackoverflow.com/questions/30549226/guzzlehttp-how-get-the-body-of-a-response-from-guzzle-6#30549372
        return (string) $this->response()->getBody();
    }

    /**
     * Returns error status.
     *
     * @return boolean
     */
    public function hasErrors() {
        if( ($this->hasErrors == true) || ($this->response == null))
            return true;

        return false;
    }
}
