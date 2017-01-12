<?php

namespace App;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

// To use: $response = HTTPResponse::get($url);
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
                'on_headers' => function (Response $response) use ($url, $takeout) {

                    if (strpos($response->getHeaderLine('Content-Type'), "text/") === false) {
                        \Log::debug('Not crawled: ' . $url);
                        $takeout->headers = $response->getHeaders();
                        throw new \Exception("File is not a text file");
                    }
                }
            ]);
            $takeout->body = $response->getBody()->getContents();
            $takeout->headers = $response->getHeaders();
        } catch (\Exception $e) {
            // Do nothing here.
            // If file is not a text file it will not be downloaded and cached.
        }

        return new CachedResponse($url, collect($takeout->headers), $takeout->body);
    }
}