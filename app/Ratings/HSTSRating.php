<?php

namespace App\Ratings;

use GuzzleHttp\Client;
use App\HTTPResponse;


class HSTSRating extends Rating
{

    public function __construct(HTTPResponse $response) {
        parent::__construct($response);

        $this->name = "STRICT_TRANSPORT_SECURITY";
        $this->scoreType = "warning";
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
            $this->testDetails->push(['placeholder' => 'HEADER_SET_MULTIPLE_TIMES', 'values' => ['HEADER' => $header]]);
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
                $this->testDetails->push(['placeholder' => 'HSTS_LESS_6', 'values' => ['HEADER' => $header]]);
            } elseif ($maxAge >= 15768000) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'HSTS_MORE_6', 'values' => ['HEADER' => $header]]);
            } else {
                $this->score = 0;
                $this->hasError = true;
                $this->errorMessage = 'MAX_AGE_ERROR';
            }

            if (strpos($header, 'includeSubDomains') !== false) {
                $this->testDetails->push(['placeholder' => 'INCLUDE_SUBDOMAINS', 'values' => ['HEADER' => $header]]);
            }

            if (strpos($header, 'preload') !== false) {
                $this->testDetails->push(['placeholder' => 'HSTS_PRELOAD', 'values' => ['HEADER' => $header]]);
            }
        }
    }
}
