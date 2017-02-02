<?php

namespace App\Ratings;

class HSTSRating extends Rating {

    protected function rate()
    {
        $header = $this->getHeader('strict-transport-security');

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

            $beginAge   = strpos($header, 'max-age=') + 8;
            $endAge     = strpos($header, ';', $beginAge);

            // if there is no semicolon | max-age=300
            if ( $endAge === false)
                $endAge = strlen($header);

            $maxAge     = substr($header, $beginAge, $endAge - $beginAge);

            if ($maxAge < 15768000) {
                $this->rating   = 'B';
                $this->comment  = __('The value for "max-age" is smaller than 6 months.');
            }
            elseif ($maxAge >= 15768000) {
                $this->rating   = 'A';
                $this->comment  = __('The value for "max-age" is greater than 6 months.');
            }
            else {
                $this->rating   = 'C';
                $this->comment  = __('An error occured while checking "max-age".');
            }
        }

        if (strpos($header, 'includeSubDomains') !== false ) {
            $this->rating   .= '+';
            $this->comment  .= '\n' . __('"includeSubDomains" is set.');
        }

        if (strpos($header, 'preload') !== false) {
            $this->rating   .= '+';
            $this->comment  .= '\n' . __('"preload" is set.');
        }
    }

    public static function getDescription()
    {
        // OWASP Secure Headers Project
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#HTTP_Strict_Transport_Security_.28HSTS.29
        // TODO: Translate
        return 'HTTP Strict Transport Security (HSTS) is a web security policy mechanism which helps to protect websites against protocol downgrade attacks and cookie hijacking. It allows web servers to declare that web browsers (or other complying user agents) should only interact with it using secure HTTPS connections, and never via the insecure HTTP protocol.';
    }

    public static function getBestPractice()
    {
        // OWASP Best Practice
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#hsts
        return 'Strict-Transport-Security "max-age=63072000; includeSubdomains"';
    }
}