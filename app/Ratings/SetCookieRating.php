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

                    if ($cookie->isSecureOnly()) {
                        $this->score += 90;
                        $hasSecureFlag = true;
                    } else {
                        $hasSecureFlag = false;
                    }

                    if ($cookie->isHttpOnly()) {
                        $this->score += 10;
                        $hasHttpOnlyFlag = true;
                    } else {
                        $hasHttpOnlyFlag = false;
                    }

                    $this->testDetails->push(TranslateableMessage::get('COOKIE', [
                        'NAME' => $cookie->getName(),
                        'HTTPONLY' => $hasHttpOnlyFlag ? '🗸' : '✗',
                        'SECURE' => $hasSecureFlag ? '🗸' : '✗',
                    ]));
                }
                // Set-Cookie header exists but not valid so $cookie = null
                else {
                    $this->score -= 5;
                    $this->testDetails->push(TranslateableMessage::get('INVALID_HEADER', ['HEADER' => 'Set-Cookie: ' . $cookieHeader]));
                }
            }

            // Calculate average score for all cookie headers
            $this->score = (int) ceil(($this->score / count($header)));
            $this->score = $this->score > 100 ? 100 : $this->score;

            if ($this->score == 100) {
                $this->scoreType = 'success';
            }
        }
    }
}
