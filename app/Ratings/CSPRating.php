<?php

namespace App\Ratings;

use App\CSPParser;
use App\HTTPResponse;
use GuzzleHttp\Client;


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
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_SET_MULTIPLE_TIMES";
            $this->testDetails->push(['placeholder' => 'HEADER_SET_MULTIPLE_TIMES', 'values' => ['HEADER' => $header] ]);
        } else {
            $header = $header[0];
            $csp = new CSPParser($header);

            if ( ! $csp->isValid() ) {
                $this->score = 0;
                $this->hasError = true;
                $this->testDetails->push(['placeholder' => 'CSP_IS_NOT_VALID', 'values' => ['HEADER' => $header]]);
            } elseif ($csp->containsUnsafeValues()) {
                $this->score = 50;
                $this->testDetails->push(['placeholder' => 'CSP_UNSAFE_INCLUDED', 'values' => ['HEADER' => $header]]);
                $this->scoreType = "info";
            } elseif ( ! $csp->directives->has('default-src')) {
                $this->score = 0;
                $this->testDetails->push(['placeholder' => 'CSP_DEFAULT_SRC_MISSING', 'values' => ['HEADER' => $header]]);
                $this->scoreType = "info";
            } elseif ( ! $csp->containsUnsafeValues() && ! $csp->directives->get('default-src')->contains(function ($value, $key) {
                return ($value === "'self'") || ($value === "'none'");
            })) {
                $this->score = 75;
                $this->scoreType = "info";
                $this->testDetails->push(['placeholder' => 'CSP_NO_UNSAFE_INCLUDED', 'values' => ['HEADER' => $header]]);
            } elseif (! $csp->containsUnsafeValues() && $csp->directives->get('default-src')->contains(function ($value, $key) {
                return ($value === "'self'") || ($value === "'none'");
            })) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'CSP_CORRECT', 'values' => ['HEADER' => $header]]);
            }
        }

        // Check if legacy header is available
        $legacyHeader = $this->getHeader("x-content-security-policy");
        if (is_array($legacyHeader) && count ($legacyHeader) > 1) {
            $this->testDetails->push(['placeholder' => 'CSP_LEGACY_HEADER_SET', 'values' => ['HEADER' => json_encode($legacyHeader)]]);
        }
    }
}
