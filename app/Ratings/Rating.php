<?php

namespace App\Ratings;

use App\HTTPResponse;
use GuzzleHttp\Client;

abstract class Rating implements RatingInterface, \JsonSerializable
{
    protected $url;
    protected $response;
    protected $rating = 'C';
    protected $comment = 'An error occurred.';

    /**
     * Rating constructor.
     * @param $url
     * @param Client $client
     */
    public function __construct($url, Client $client = null)
    {
        $this->url = $url;

        $this->response = new HTTPResponse($this->url, $client);
        $this->rate();
    }

    public function url()
    {
        return $this->url;
    }

    public function getHeader($header)
    {
        return $this->response->header($header);
    }

    /**
     * @return string
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return ["rating" => $this->getRating(), "comment" => $this->comment];
    }
}
