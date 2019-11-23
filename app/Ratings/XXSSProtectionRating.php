<?php

namespace App\Ratings;

use App\HTTPResponse;
use App\TranslateableMessage;

class XXSSProtectionRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'X_XSS_PROTECTION';
        $this->scoreType = 'warning';

        parent::__construct($response);
    }

    protected function rate()
    {
        $header = $this->getHeader('x-xss-protection');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_NOT_SET');
        } elseif ($header === 'ERROR') {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => 'X-XSS-Protection']);
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES');
        } else {
            $header = $header[0];

            $this->score = 50;

            if (strpos($header, 'mode=block') !== false) {
                $this->score = 100;
                $this->scoreType = 'success';
                $this->testDetails->push(TranslateableMessage::get('XXSS_BLOCK'));
            } else {
                $this->testDetails->push(TranslateableMessage::get('XXSS_CORRECT'));
            }
        }
    }
}
