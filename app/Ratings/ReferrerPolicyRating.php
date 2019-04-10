<?php

namespace App\Ratings;

use App\HTTPResponse;
use App\TranslateableMessage;

class ReferrerPolicyRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'REFERRER_POLICY';
        $this->scoreType = 'bonus';

        parent::__construct($response);
    }

    protected function rate()
    {
        $header = $this->getHeader('referrer-policy');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_NOT_SET');
        } elseif ($header === 'ERROR') {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => 'Referrer-Policy']);
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES');
        } else {
            $header = $header[0];

            if ($header == 'no-referrer') {
                $this->score = 100;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'NO_REFERRER']));
            } elseif ($header == 'same-origin') {
                $this->score = 100;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'SAME_ORIGIN']));
            } elseif ($header == 'strict-origin') {
                $this->score = 70;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'STRICT_ORIGIN']));
            } elseif ($header == 'strict-origin-when-cross-origin') {
                $this->score = 70;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'STRICT_ORIGIN_WHEN_CROSS_ORIGIN']));
            } elseif ($header == 'origin') {
                $this->score = 40;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'ORIGIN']));
            } elseif ($header == 'origin-when-cross-origin') {
                $this->score = 40;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'ORIGIN_WHEN_CROSS_ORIGIN']));
            } elseif (empty($header)) {
                $this->score = 10;
                $this->testDetails->push(TranslateableMessage::get('EMPTY_DIRECTIVE'));
            } elseif ($header == 'no-referrer-when-downgrade') {
                $this->score = 0;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'NO_REFERRER_WHEN_DOWNGRADE']));
            } elseif ($header == 'unsafe-url') {
                $this->score = 0;
                $this->testDetails->push(TranslateableMessage::get('DIRECTIVE_SET', ['DIRECTIVE' => 'UNSAFE_URL']));
            } else {
                $this->score = 0;
                $this->hasError = true;
                $this->errorMessage = TranslateableMessage::get('WRONG_DIRECTIVE_SET');
            }
        }
    }
}
