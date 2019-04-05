<?php

namespace App\Ratings;

use App\HTTPResponse;
use App\TranslateableMessage;
use Delight\Cookie\Cookie;

class SetCookieRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'SET_COOKIE';
        $this->scoreType = 'hidden';

        parent::__construct($response);
    }

    protected function rate()
    {
        $header = $this->getHeader('set-cookie');

        if ($header === null) {
            // do nothing
        } elseif ($header === 'ERROR') {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => 'Set-Cookie']);
        } else {
            $this->scoreType = 'warning';

            foreach ($header as $cookieHeader) {
                $cookie = Cookie::parse('Set-Cookie: ' . $cookieHeader);
                if ($cookie) {
                    // Check for Secure Flag
                    if ($cookie->isSecureOnly()) {
                        $this->score += 90;
                        $this->testDetails->push(TranslateableMessage::get('SECURE_FLAG_SET', ['COOKIE' => $cookieHeader]));
                    } else {
                        $this->testDetails->push(TranslateableMessage::get('NO_SECURE_FLAG_SET', ['COOKIE' => $cookieHeader]));
                    }

                    // Check for HttpOnly Flag
                    if ($cookie->isHttpOnly()) {
                        $this->score += 10;
                        $this->testDetails->push(TranslateableMessage::get('HTTPONLY_FLAG_SET', ['COOKIE' => $cookieHeader]));
                    } else {
                        $this->testDetails->push(TranslateableMessage::get('NO_HTTPONLY_FLAG_SET', ['COOKIE' => $cookieHeader]));
                    }
                }
                // Set-Cookie header exists but not valid so $cookie = null
                else {
                    $this->score -= 5;
                    $this->testDetails->push(TranslateableMessage::get('INAVLID_HEADER', ['HEADER' => 'Set-Cookie: ' . $cookieHeader]));
                }
            }

            // Calculate average score for all cookie headers
            $this->score = (int)ceil(($this->score / count($header)));
            $this->score = $this->score > 100 ? 100 : $this->score;
        }
    }
}
