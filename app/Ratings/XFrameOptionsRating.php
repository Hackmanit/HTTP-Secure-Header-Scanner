<?php

namespace App\Ratings;

class XFrameOptionsRating extends Rating
{

    protected function rate()
    {
        $header = $this->getHeader('x-frame-options');

        if ($header === null) {
            $this->rating   = 'C';
            $this->comment  = __('The header is not set.');
        }

        elseif (count($header) > 1) {
            $this->rating   = 'C';
            $this->comment  = __('The header is set multiple times.');
        }

        else {
            $header = $header[0];

            $this->rating   = 'A';
            $this->comment  = __('The header is set and does not contain any wildcard.');

            if (strpos($header, '*') !== false) {
                $this->rating   = 'C';
                $this->comment  = __('The header contains wildcards and is thereby useless.');
            }
        }
    }

    public static function getDescription()
    {
        // OWASP Secure Headers Project
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#X-Frame-Options
        // TODO: Translate
        return 'X-Frame-Options response header improve the protection of web applications against Clickjacking. It declares a policy communicated from a host to the client browser on whether the browser must not display the transmitted content in frames of other web pages.';
    }

    public static function getBestPractice()
    {
        // Hackmanit
        // TODO: Check and Translate
        return 'Best Practice is to set this header accordingly to your needs. Do not use "allow-from: *".';
    }
}