<?php

namespace App\Ratings;

use App\HTTPResponse;

abstract class Rating implements RatingInterface, \JsonSerializable
{
    protected $url;
    protected $rating;
    protected $comment;

    public function __construct($url)
    {
        $this->url = $url;
        $this->rate();
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
     * @param $lowercaseHeader string header name in lowercase
     * @return array
     */
    public function getHeader($lowercaseHeader)
    {
        return HTTPResponse::get($this->url)->getHeaders()->mapWithKeys(function( $value, $key ) {
            return [strtolower($key) => $value];
        })->get($lowercaseHeader, []);
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