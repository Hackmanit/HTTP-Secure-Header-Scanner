<?php

namespace App;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

/**
 * This class is used to save the GuzzleHttp Response.
 * The response cannot be saved directly because it uses a PHP Stream that could not be saved in the Redis cache.
 *
 * Class CachedResponse
 * @package App\Http\Controllers
 */
class CachedResponse
{
    protected $url;
    protected $headers;
    protected $body;

    function __construct($url, Collection $headers, $body)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;

        Redis::hset("response", $url, serialize($this));
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return Collection
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}