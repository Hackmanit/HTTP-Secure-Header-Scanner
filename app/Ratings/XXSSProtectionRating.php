<?php

namespace App\Ratings;

use GuzzleHttp\Client;


class XXSSProtectionRating extends Rating
{
    
    public function __construct($url, Client $client = null) {
        parent::__construct($url, $client);

        $this->name = "X_XSS_PROTECTION";
        $this->scoreType = "critical";
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
