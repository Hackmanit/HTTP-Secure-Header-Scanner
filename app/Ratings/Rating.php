<?php

namespace App\Ratings;

use App\HTTPResponse;
use GuzzleHttp\Client;

abstract class Rating
{
    protected $url;
    protected $response;

    public $name = "TO BE SET!";
    public $hasError = false;
    public $errorMessage = null;
    public $score = 0;
    public $scoreType = "TO BE SET!";
    public $testDetails = null;


    /**
     * Rating constructor.
     * @param $url
     * @param Client $client
     */
    public function __construct($url, Client $client = null)
    {
        $this->url = $url;
        $this->testDetails = collect();

        $this->response = new HTTPResponse($this->url, $client);
        $this->rate();
    }


    public function getHeader($header)
    {
        return $this->response->header($header);
    }

}
