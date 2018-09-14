<?php

namespace App\Ratings;

use App\CSPParser;
use App\HTTPResponse;
use App\TranslateableMessage;

class CSPRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        parent::__construct($response);

        $this->name = 'CONTENT_SECURITY_POLICY';
        $this->scoreType = 'warning';
    }

    protected function rate()
    {
        $header = $this->getHeader('content-security-policy');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_NOT_SET');
        } elseif ($header === 'ERROR') {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => 'Content-Security-Policy']);
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES', ['HEADER' => $header]);
        } else {
            $header = $header[0];
            $csp = new CSPParser($header);

            if (!$csp->isValid()) {
                $this->score = 0;
                $this->hasError = true;
                $this->errorMessage = TranslateableMessage::get('CSP_IS_NOT_VALID', ['HEADER' => $header]);
            } elseif ($csp->containsUnsafeValues()) {
                $this->score = 50;
                $this->testDetails->push(TranslateableMessage::get('CSP_UNSAFE_INCLUDED', ['HEADER' => $header]));
                $this->scoreType = 'info';
            } elseif (!$csp->directives->has('default-src')) {
                $this->score = 0;
                $this->testDetails->push(TranslateableMessage::get('CSP_DEFAULT_SRC_MISSING', ['HEADER' => $header]));
                $this->scoreType = 'info';
            } elseif (!$csp->containsUnsafeValues() && !$csp->directives->get('default-src')->contains(function ($value, $key) {
                return ($value === "'self'") || ($value === "'none'");
            })) {
                $this->score = 75;
                $this->scoreType = 'info';
                $this->testDetails->push(TranslateableMessage::get('CSP_NO_UNSAFE_INCLUDED', ['HEADER' => $header]));
            } elseif (!$csp->containsUnsafeValues() && $csp->directives->get('default-src')->contains(function ($value, $key) {
                return ($value === "'self'") || ($value === "'none'");
            })) {
                $this->score = 100;
                $this->testDetails->push(TranslateableMessage::get('CSP_CORRECT', ['HEADER' => $header]));
            }
        }

        // Check if legacy header is available
        $legacyHeader = $this->getHeader('X-Content-Security-Policy');
        if (is_array($legacyHeader) && count($legacyHeader) > 0) {
            $this->testDetails->push(TranslateableMessage::get('CSP_LEGACY_HEADER_SET', ['HEADER_NAME' => 'X-Content-Security-Policy']));
        }

        // Check if legacy header X-WebKit-CSP is available
        $legacyHeader = $this->getHeader('X-WebKit-CSP');
        if (is_array($legacyHeader) && count($legacyHeader) > 0) {
            $this->testDetails->push(TranslateableMessage::get('CSP_LEGACY_HEADER_SET', ['HEADER_NAME' => 'X-WebKit-CSP']));
        }
    }
}
