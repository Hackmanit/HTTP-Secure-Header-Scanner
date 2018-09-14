<?php

namespace App\Ratings;

use App\HTTPResponse;
use App\TranslateableMessage;

class XXSSProtectionRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        parent::__construct($response);

        $this->name = 'X_XSS_PROTECTION';
        $this->scoreType = 'warning';
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
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES', ['HEADER' => $header]);
        } else {
            $header = $header[0];

            $this->score = 50;
            $this->testDetails->push(TranslateableMessage::get('XXSS_CORRECT', ['HEADER' => $header]));

            if (strpos($header, 'mode=block') !== false) {
                $this->score = 100;
                $this->testDetails->push(TranslateableMessage::get('XXSS_BLOCK', ['HEADER' => $header]));
            }
        }
    }
}
