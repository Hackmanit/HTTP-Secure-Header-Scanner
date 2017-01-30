<?php

namespace App\Ratings;

use App\HTTPResponse;

abstract class Rating implements RatingInterface, \JsonSerializable
{
    protected $url;
    protected $rating;
    protected $comment;

    /**
     * Rating constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->rate();
    }

    public function getHeader($header)
    {
        return (new HTTPResponse($this->url))->header($header);
    }

    /**
     * @return string
     */
    public function getRating() {
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
    function jsonSerialize()
    {
        return ["rating" => $this->getRating(), "comment" => $this->comment];
    }
}