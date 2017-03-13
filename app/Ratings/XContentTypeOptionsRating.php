<?php

namespace App\Ratings;

class XContentTypeOptionsRating extends Rating
{

    protected function rate()
    {
        $header = $this->getHeader('x-content-type-options');

        if ($header === null) {
            $this->rating = 'C';
            $this->comment  = __('The header is not set.');
        }

        elseif (count($header) > 1) {
            $this->rating = 'C';
            $this->comment  = __('The header is set multiple times.');
        }

        else {
            $header = $header[0];

            $this->rating = 'C';
            $this->comment = __('The header is not set correctly.');

            if (strpos($header, 'nosniff') !== false) {
                $this->rating = 'A';
                $this->comment = __('The header is set correctly.');
            }

        }
    }

    public static function getDescription()
    {
        // OWASP
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#X-Content-Type-Options
        // TODO: Translate
        return 'Setting this header will prevent the browser from interpreting files as something else than declared by the content type in the HTTP headers.';
    }

    public static function getBestPractice()
    {
        // OWASP 
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#xcto
        return 'nosniff';
    }
}