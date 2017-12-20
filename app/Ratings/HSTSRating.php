<?php

namespace App\Ratings;

use GuzzleHttp\Client;


class HSTSRating extends Rating
{

    public function __construct($url, Client $client = null) {
        parent::__construct($url, $client);

        $this->name = "STRICT_TRANSPORT_SECURITY";
        $this->scoreType = "critical";
    }

    protected function rate()
    {
        $header = $this->getHeader('strict-transport-security');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_NOT_SET";
        } elseif (count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_SET_MULTIPLE_TIMES";
        } else {
            $header = $header[0];

            $beginAge   = strpos($header, 'max-age=') + 8;
            $endAge     = strpos($header, ';', $beginAge);

            // if there is no semicolon | max-age=300
            if ($endAge === false) {
                $endAge = strlen($header);
            }

            $maxAge     = substr($header, $beginAge, $endAge - $beginAge);

            if ($maxAge < 15768000) {
                $this->score = 60;
                $this->testDetails->push(['placeholder' => 'HSTS_LESS_6']);
            } elseif ($maxAge >= 15768000) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'HSTS_MORE_6']);
            } else {
                $this->score = 0;
                $this->hasError = true;
                $this->errorMessage = 'MAX_AGE_ERROR';
            }
        }

        if (strpos($header, 'includeSubDomains') !== false) {
            $this->testDetails->push(['placeholder' => 'INCLUDE_SUBDOMAINS']);
        }

        if (strpos($header, 'preload') !== false) {
            $this->testDetails->push(['placeholder' => 'HSTS_PRELOAD']);
        }
    }

}
