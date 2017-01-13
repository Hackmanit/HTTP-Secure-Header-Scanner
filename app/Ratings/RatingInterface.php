<?php

namespace App\Ratings;

interface RatingInterface {
    public function __construct($url);
    public static function getDescription();
    public static function getBestPractice();
    public function getHeader();
    public function getRating();
    public function getComment();
}