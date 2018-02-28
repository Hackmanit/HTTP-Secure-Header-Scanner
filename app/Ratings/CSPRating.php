<?php

namespace App\Ratings;

use GuzzleHttp\Client;


class CSPRating extends Rating
{

    public function __construct($url, Client $client = null) {
        parent::__construct($url, $client);

        $this->name = "CONTENT_SECURITY_POLICY";
        $this->scoreType = "critical";
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
        } else {
            $header = $header[0];

            $this->testDetails->push(['placeholder' => 'HEADER', 'values' => [ $header ]]);

            if (strpos($header, 'unsafe-inline') !== false || strpos($header, 'unsafe-eval') !== false) {
                $this->score = 0;
                $this->testDetails->push(['placeholder' => 'CSP_UNSAFE_INCLUDED']);
            } elseif (strpos($header, 'unsafe-inline') === false && strpos($header, 'unsafe-eval') === false && strpos($header, "default-src 'none'") === false) {
                $this->score = 50;
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
