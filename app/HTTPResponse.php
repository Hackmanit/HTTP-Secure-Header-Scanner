<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class HTTPResponse
{
    protected $url;
    protected $response = null;
    protected $hasErrors = false;

    public function __construct($url, Client $client = null)
    {
        $this->url = $this->punycodeUrl($url);
        Log::info('Scanning the following URL: '.$this->url);

        $this->client = $client;

        $this->calculateResponse();
    }

    /**
     * Calculates the HTTPResponse.
     *
     * @return void
     */
    protected function calculateResponse()
    {
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
                    'verify'      => false,
                    'http_errors' => false,
                ]);
            } catch (\Exception $exception) {
                Log::warning($this->url.': '.$exception);
                $this->hasErrors = true;
            }
        }
    }

    /**
     * Returns the GuzzleHttp Response.
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
        if ($this->hasErrors()) {
            return;
        }

        return $this->response()->getStatusCode();
    }

    /**
     * @return \Illuminate\Support\Collection HTTP headers
     */
    public function headers()
    {
        if ($this->hasErrors()) {
            return;
        }

        return collect($this->response()->getHeaders());
    }

    /**
     * @param $name string header name in lowercase
     *
     * @return array
     */
    public function header($name)
    {
        if ($this->hasErrors()) {
            return;
        }

        return $this->headers()->mapWithKeys(function ($value, $key) {
            return [strtolower($key) => $value];
        })->get(strtolower($name));
    }

    /**
     * @return string HTTP Body
     */
    public function body()
    {
        if ($this->hasErrors()) {
            return;
        }

        // Fixed empty body
        // See: https://stackoverflow.com/questions/30549226/guzzlehttp-how-get-the-body-of-a-response-from-guzzle-6#30549372
        return (string) $this->response()->getBody();
    }

    /**
     * Returns error status.
     *
     * @return bool
     */
    public function hasErrors()
    {
        if (($this->hasErrors == true) || ($this->response == null)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the Punycode encoded URL for a given URL.
     *
     * @param string $url URL to encode
     *
     * @return string Punycode-Encoded URL.
     */
    public function punycodeUrl($url)
    {
        $parsed_url = parse_url($url);

        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'].'://' : '';
        $host = isset($parsed_url['host']) ? idn_to_ascii($parsed_url['host'], IDNA_NONTRANSITIONAL_TO_ASCII,INTL_IDNA_VARIANT_UTS46) : '';
        $port = isset($parsed_url['port']) ? ':'.$parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':'.$parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?'.$parsed_url['query'] : '';

        return "$scheme$user$pass$host$port$path$query";
    }
}
