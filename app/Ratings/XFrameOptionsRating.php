<?php

namespace App\Ratings;

use GuzzleHttp\Client;
use App\HTTPResponse;


class XFrameOptionsRating extends Rating
{

    public function __construct(HTTPResponse $response) {
        parent::__construct($response);

        $this->name = "X_FRAME_OPTIONS";
        $this->scoreType = "warning";
    }

    protected function rate()
    {
        $header = $this->getHeader('x-frame-options');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_NOT_SET";
        } elseif (count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_SET_MULTIPLE_TIMES";
            $this->testDetails->push(['placeholder' => 'HEADER', 'values' => [ $header ]]);
        } else {
            $header = $header[0];

            $this->testDetails->push(['placeholder' => 'HEADER', 'values' => [ $header ]]);

            if (strpos($header, '*') !== false) {
                $this->score = 0;
                $this->testDetails->push(['placeholder' => 'XFO_WILDCARDS']);
            }
            else {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'XFO_CORRECT']);
            }
        }
    }
}
