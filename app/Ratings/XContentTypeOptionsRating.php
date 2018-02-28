<?php

namespace App\Ratings;

use GuzzleHttp\Client;


class XContentTypeOptionsRating extends Rating
{

    public function __construct($url, Client $client = null) {
        parent::__construct($url, $client);

        $this->name = "X_CONTENT_TYPE_OPTIONS";
        $this->scoreType = "critical";
    }

    protected function rate()
    {
        $header = $this->getHeader('x-content-type-options');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_NOT_SET";
        } elseif (count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_SET_MULTIPLE_TIMES";
        } else {
            $header = $header[0];

            $this->testDetails->push(['placeholder' => 'HEADER', 'values' => [ $header ]]);

            if (strpos($header, 'nosniff') !== false) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'XCTO_CORRECT']);
            }
            else {
                $this->testDetails->push(['placeholder' => 'XCTO_NOT_CORRECT']);
            }
        }
    }
}
