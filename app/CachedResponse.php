<?php

namespace App;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Redis;

/**
 * This class is used to save the GuzzleHttp Response.
 * The resonse cannot be saved directly because it uses a PHP Stream that could not be saved in the Redis cache.
 *
 * Class CachedResponse
 * @package App\Http\Controllers
 */
class CachedResponse
{
    protected $id;
    protected $url;
    protected $headers;
    protected $body;

    function __construct($id, $url, Response $response)
    {
        $this->id = $id;
        $this->url = $url;
        $this->headers = collect($response->getHeaders());
        $this->body = $response->getBody()->getContents();

        Redis::hset("response", $url, serialize($this));
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
        return $this->getHeaders();
    }
}