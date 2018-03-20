<?php

namespace App\Ratings;

use GuzzleHttp\Client;
use App\HTTPResponse;


class CSPRating extends Rating
{

    public function __construct(HTTPResponse $response) {
        parent::__construct($response);

        $this->name = "CONTENT_SECURITY_POLICY";
        $this->scoreType = "warning";
    }


    protected function rate()
    {
        $header = $this->getHeader('content-security-policy');

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

            if (strpos($header, 'unsafe-inline') !== false || strpos($header, 'unsafe-eval') !== false) {
                $this->score = 50;
                $this->testDetails->push(['placeholder' => 'CSP_UNSAFE_INCLUDED']);
                $this->scoreType = "info";
            } elseif (strpos($header, 'unsafe-inline') === false && strpos($header, 'unsafe-eval') === false && strpos($header, "default-src 'none'") === false) {
                $this->score = 75;
                $this->scoreType = "info";
                $this->testDetails->push(['placeholder' => 'CSP_NO_UNSAFE_INCLUDED']);
            } elseif (strpos($header, 'unsafe-inline') === false && strpos($header, 'unsafe-eval') === false && strpos($header, "default-src 'none'") !== false) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'CSP_CORRECT']);
            }
        }

        // Check if legacy header is available
        if (count($this->getHeader("x-content-security-policy")) > 0) {
            $this->testDetails->push(['placeholder' => 'CSP_LEGACY_HEADER_SET']);
        }
    }
}
