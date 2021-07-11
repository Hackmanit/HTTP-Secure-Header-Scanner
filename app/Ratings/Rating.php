<?php

namespace App\Ratings;

use App\HTTPResponse;
use voku\helper\HtmlDomParser;

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
        $result = $this->response->header($header);

        return json_encode($result) ? $result : 'ERROR';
    }

    /**
     * Return the HTML-Content of a site as a SimpleHtmlDom or false.
     *
     * @return bool|voku\helper\SimpleHtmlDom
     */
    public function getBody()
    {
        return HtmlDomParser::str_get_html($this->response->body());
    }
}
