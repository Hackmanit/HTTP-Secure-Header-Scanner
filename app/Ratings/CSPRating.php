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
        } elseif ($header === "ERROR") {
            $this->hasError = true;
            $this->errorMessage = "HEADER_ENCODING_ERROR";
            $this->testDetails->push([
                'placeholder' => 'HEADER_ENCODING_ERROR',
                'values' => [
                    'HEADER_NAME' => "Content-Security-Policy"
                ]
            ]);
        } elseif (count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_SET_MULTIPLE_TIMES";
            $this->testDetails->push(['placeholder' => 'HEADER_SET_MULTIPLE_TIMES', 'values' => ['HEADER' => $header] ]);
        } else {
            $header = $header[0];

            if (strpos($header, 'unsafe-inline') !== false || strpos($header, 'unsafe-eval') !== false) {
                $this->score = 50;
                $this->testDetails->push(['placeholder' => 'CSP_UNSAFE_INCLUDED', 'values' => ['HEADER' => $header]]);
                $this->scoreType = "info";
            } elseif (strpos($header, 'unsafe-inline') === false && strpos($header, 'unsafe-eval') === false && preg_match("/default-src\s+'none'/", $header) === 0) {
                $this->score = 75;
                $this->scoreType = "info";
                $this->testDetails->push(['placeholder' => 'CSP_NO_UNSAFE_INCLUDED', 'values' => ['HEADER' => $header]]);
            } elseif (strpos($header, 'unsafe-inline') === false && strpos($header, 'unsafe-eval') === false && preg_match("/default-src\s+'none'/", $header) === 1) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'CSP_CORRECT', 'values' => ['HEADER' => $header]]);
            }
        }

        // Check if legacy header is available
        $legacyHeader = $this->getHeader("x-content-security-policy");
        if (count($legacyHeader) > 0) {
            $this->testDetails->push(['placeholder' => 'CSP_LEGACY_HEADER_SET', 'values' => ['HEADER' => json_encode($legacyHeader)]]);
        }
    }
}
