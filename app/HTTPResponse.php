<?php

namespace App;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

class HTTPResponse
{
    /**
     * Returns the (cached) GuzzleHttp Response
     *
     * @param $url
     * @return CachedResponse
     */
    public static function get($url)
    {
        $cached = Redis::hget("response", $url);
        if ($cached)
            return unserialize($cached);

        $client = new Client();
        $takeout = new \stdClass;
        $takeout->body = null;
        $takeout->headers = null;

        try {
            $response = $client->request('GET', $url, [
                // User-Agent because some sites (e.g. facebook) do not return all headers if the user-agent is missing or Guzzle
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
                    ],
                //'proxy' => 'http://172.18.0.1:8080',
                'verify' => false,

                'on_headers' => function (Response $response) use ($url, $takeout) {
                    if ($response->getHeaderLine('Content-Length') > 1024*10) {
                        throw new \Exception('The file is too big!');
                    }
                }
            ]);
            $takeout->body = $response->getBody()->getContents();
            $takeout->headers = $response->getHeaders();
        } catch (\Exception $e) {
            // Do nothing here.
            // If file is not a text file it will not be downloaded and cached.
            \Log::debug("Error: " . $e);
        }

        return new CachedResponse($url, collect($takeout->headers), $takeout->body);
    }
}