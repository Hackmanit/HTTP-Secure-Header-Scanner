<?php

namespace App\Ratings;

use App\HTTPResponse;
use App\TranslateableMessage;

class HSTSRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'STRICT_TRANSPORT_SECURITY';
        $this->scoreType = 'warning';

        parent::__construct($response);
    }

    protected function rate()
    {
        $header = $this->getHeader('strict-transport-security');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_NOT_SET');
        } elseif ($header === 'ERROR') {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => 'Strict-Transport-Security']);
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES');
        } else {
            $header = $header[0];

            $beginAge = strpos($header, 'max-age=') + 8;
            $endAge = strpos($header, ';', $beginAge);

            // if there is no semicolon | max-age=300
            if ($endAge === false) {
                $endAge = strlen($header);
            }

            $maxAge = substr($header, $beginAge, $endAge - $beginAge);

            if ($maxAge < 15768000) {
                $this->score = 60;
                $this->testDetails->push(TranslateableMessage::get('HSTS_LESS_6'));
            } elseif ($maxAge >= 15768000) {
                $this->score = 100;
                $this->scoreType = 'success';
                $this->testDetails->push(TranslateableMessage::get('HSTS_MORE_6'));
            } else {
                $this->score = 0;
                $this->hasError = true;
                $this->errorMessage = TranslateableMessage('MAX_AGE_ERROR');
            }

            if (strpos($header, 'includeSubDomains') !== false) {
                $this->testDetails->push(TranslateableMessage::get('INCLUDE_SUBDOMAINS'));
            }

            if (strpos($header, 'preload') !== false) {
                $this->testDetails->push(TranslateableMessage::get('HSTS_PRELOAD'));
            }
        }
    }
}
