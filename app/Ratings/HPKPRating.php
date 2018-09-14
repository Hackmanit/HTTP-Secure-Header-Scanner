<?php

namespace App\Ratings;

use GuzzleHttp\Client;
use App\HTTPResponse;
use App\TranslateableMessage;


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
            $this->errorMessage = TranslateableMessage::get("HEADER_NOT_SET");
        } elseif ($header === "ERROR") {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => "Public-Key-Pins"]);
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES', ['HEADER' => $header]);
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
                $this->testDetails->push(TranslateableMessage::get('HPKP_LESS_15', ['HEADER' => $header]));
            } elseif ($maxAge >= 1296000) {
                $this->testDetails->push(TranslateableMessage::get('HPKP_MORE_15', ['HEADER' => $header]));
            } else {
                $this->score   = 0;
                $this->hasError = true;
                $this->errorMessage = TranslateableMessage::get('MAX_AGE_ERROR');
            }

            if (strpos($header, 'includeSubDomains') !== false) {
                $this->testDetails->push(TranslateableMessage::get('INCLUDE_SUBDOMAINS', ['HEADER' => $header]));
            }

            if (strpos($header, 'report-uri') !== false) {
                $this->testDetails->push(TranslateableMessage::get('HPKP_REPORT_URI', ['HEADER' => $header]));
            }
        }
    }
}
