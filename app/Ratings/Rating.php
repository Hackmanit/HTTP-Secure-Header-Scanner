<?php

namespace App\Ratings;

abstract class Rating implements RatingInterface
{
    protected $url;
    protected $rating;
    protected $comment;

    public function __construct($url)
    {
        $this->url = $url;
        $this->rate();
    }

    public function getRating() {
        return $this->rating;
    }

    public function getComment()
    {
        return $this->comment;
    }
}