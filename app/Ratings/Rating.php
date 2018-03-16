<?php

namespace App\Ratings;

use App\HTTPResponse;
use GuzzleHttp\Client;

abstract class Rating
{
    protected $response;

    public $name = null;
    public $hasError = false;
    public $errorMessage = null;
    public $score = 0;
    public $scoreType = null;
    public $testDetails = null;


    /**
     * Rating constructor.
     */
    public function __construct(HTTPResponse $response)
    {
        $this->response = $response;
        $this->testDetails = collect();

        $this->rate();
    }


    public function getHeader($header)
    {
        return $this->response->header($header);
    }

}
