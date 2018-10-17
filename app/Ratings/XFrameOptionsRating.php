<?php

namespace App\Ratings;

use App\HTTPResponse;
use App\TranslateableMessage;

class XFrameOptionsRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'X_FRAME_OPTIONS';
        $this->scoreType = 'warning';

        parent::__construct($response);
    }

    protected function rate()
    {
        $header = $this->getHeader('x-frame-options');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_NOT_SET');
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES', ['HEADER' => $header]);
        } elseif ($header === 'ERROR') {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => 'X-Frame-Options']);
        } else {
            $header = $header[0];

            if (strpos($header, '*') !== false) {
                $this->score = 0;
                $this->testDetails->push(TranslateableMessage::get('XFO_WILDCARDS', ['HEADER' => $header]));
            } else {
                $this->score = 100;
                $this->testDetails->push(TranslateableMessage::get('XFO_CORRECT', ['HEADER' => $header]));
            }
        }
    }
}
