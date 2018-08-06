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
            $this->testDetails->push(['placeholder' => 'HEADER_SET_MULTIPLE_TIMES', 'values' => ['HEADER' => $header]]);
        } elseif ($header === "ERROR") {
            $this->hasError = true;
            $this->errorMessage = "HEADER_ENCODING_ERROR";
            $this->testDetails->push([
                'placeholder' => 'HEADER_ENCODING_ERROR',
                'values' => [
                    'HEADER_NAME' => 'X-Frame-Options'
                ]
            ]);
        } else {
            $header = $header[0];

            if (strpos($header, '*') !== false) {
                $this->score = 0;
                $this->testDetails->push(['placeholder' => 'XFO_WILDCARDS', 'values' => ['HEADER' => $header]]);
            }
            else {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'XFO_CORRECT', 'values' => ['HEADER' => $header]]);
            }
        }
    }
}
