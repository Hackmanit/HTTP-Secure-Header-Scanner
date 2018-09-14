<?php

namespace App\Ratings;

use GuzzleHttp\Client;
use App\HTTPResponse;
use App\TranslateableMessage;


class XContentTypeOptionsRating extends Rating
{

    public function __construct(HTTPResponse $response) {
        parent::__construct($response);

        $this->name = "X_CONTENT_TYPE_OPTIONS";
        $this->scoreType = "warning";
    }

    protected function rate()
    {
        $header = $this->getHeader('x-content-type-options');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get("HEADER_NOT_SET");
        } elseif ($header === "ERROR") {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get("HEADER_ENCODING_ERROR", ['HEADER_NAME' => "X-Content-Type-Options"]);
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES', ['HEADER' => $header]);
        } else {
            $header = $header[0];

            if ($header === 'nosniff') {
                $this->score = 100;
                $this->testDetails->push(TranslateableMessage::get('XCTO_CORRECT', ['HEADER' => $header]));
            }
            else {
                $this->testDetails->push(TranslateableMessage::get('XCTO_NOT_CORRECT', ['HEADER' => $header]));
            }
        }
    }
}
