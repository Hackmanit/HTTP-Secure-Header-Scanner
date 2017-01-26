<?php

namespace App;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

class HTTPResponse
{
    protected $url;
    protected $originalURL;
    protected $response;
    protected $getCounter = 0;
    protected $return;

    function __construct( $url )
    {
        $this->url = $url;
        $this->originalURL = $url;
        $this->response = null;
    }

    /**
     * Returns the (cached) GuzzleHttp Response
     *
     * @return CachedResponse
     */
    public function get()
    {
        $this->getCounter++;

        $cached = Redis::hget("response", $this->url);
        if ($cached)
            return unserialize($cached);

        $client = new Client(['allow_redirects' => false]);

        try {
            $this->response = $client->request( 'GET', $this->url, [
                // User-Agent because some sites (e.g. facebook) do not return all headers if the user-agent is missing or Guzzle
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
                ],
                'verify' => false,
                'http_errors' => false,

                'on_headers' => function (Response $intermediateResponse) {

                    if ($intermediateResponse === null)
                        throw new \Exception("Empty Response");

                    if ($intermediateResponse->getStatusCode() == 301 || $intermediateResponse->getStatusCode() == 302) {
                        $this->url = $intermediateResponse->getHeader( 'Location' )[0];
                    }
                }
            ] );

            // TODO: Improve this to not use recursive method calls!
            if ($this->response->getStatusCode() == 200)
                $this->return = (new CachedResponse($this->originalURL, collect($this->response->getHeaders()), $this->response->getBody(), $this->response->getStatusCode()));
            elseif ($this->getCounter <= 5)
                $this->get();
            else
                $this->return = null;
        } catch (\Exception $e) {
            $this->return =  null;
        }


        return $this->return;
    }

    /**
     * Helper wrapper for the CachedResponse getBody method.
     * @return string
     */
    public function getBody() {
        return $this->get()->getBody();
    }

    public static function checkConnection($url) {

    }

}