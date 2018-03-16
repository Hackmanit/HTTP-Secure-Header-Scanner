<?php

namespace App\Ratings;

use GuzzleHttp\Client;
use App\HTTPResponse;


class XXSSProtectionRating extends Rating
{
    
    public function __construct(HTTPResponse $response) {
        parent::__construct($response);

        $this->name = "X_XSS_PROTECTION";
        $this->scoreType = "warning";
    }

    protected function rate()
    {
        $header = $this->getHeader('x-xss-protection');

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

            $this->score = 50;
            $this->testDetails->push(['placeholder' => 'XXSS_CORRECT']);

            if (strpos($header, 'mode=block') !== false) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'XXSS_BLOCK']);
            }
        }
    }

}
