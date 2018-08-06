<?php

namespace App\Ratings;

use GuzzleHttp\Client;
use App\HTTPResponse;


class HPKPRating extends Rating
{

    public function __construct(HTTPResponse $response) {
        parent::__construct($response);

        $this->name = "PUBLIC_KEY_PINS";
        $this->scoreType = "bonus";
    }

    protected function rate()
    {
        $header = $this->getHeader('public-key-pins');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_NOT_SET";
        } elseif ($header === "ERROR") {
            $this->hasError = true;
            $this->errorMessage = "HEADER_ENCODING_ERROR";
            $this->testDetails->push([
                'placeholder' => 'HEADER_ENCODING_ERROR',
                'values' => [
                    'HEADER_NAME' => "Public-Key-Pins"
                ]
            ]);
        } elseif (count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_SET_MULTIPLE_TIMES";
            $this->testDetails->push([ 'placeholder' => 'HEADER_SET_MULTIPLE_TIMES', 'values' => ['HEADER' => $header]] );
        } else {
            $header = $header[0];

            $beginAge = strpos($header, 'max-age=') + 8;
            $endAge = strpos($header, ';', $beginAge);
            // if there is no semicolon | max-age=300
            if ($endAge === false) {
                $endAge = strlen($header);
            }

            $maxAge = substr($header, $beginAge, $endAge - $beginAge);

            $this->score = 100;

            if ($maxAge < 1296000) {
                $this->testDetails->push(['placeholder' => 'HPKP_LESS_15', 'values' => ['HEADER' => $header]]);
            } elseif ($maxAge >= 1296000) {
                $this->testDetails->push(['placeholder' => 'HPKP_MORE_15', 'values' => ['HEADER' => $header]]);
            } else {
                $this->score   = 0;
                $this->hasError = true;
                $this->errorMessage = 'MAX_AGE_ERROR';
            }

            if (strpos($header, 'includeSubDomains') !== false) {
                $this->testDetails->push(['placeholder' => 'INCLUDE_SUBDOMAINS', 'values' => ['HEADER' => $header]]);
            }

            if (strpos($header, 'report-uri') !== false) {
                $this->testDetails->push(['placeholder' => 'HPKP_REPORT_URI', 'values' => ['HEADER' => $header]]);
            }
        }
    }
}
