<?php

namespace App\Ratings;

class XXSSProtectionRating extends Rating
{

    protected function rate()
    {
        $header = $this->getHeader('x-xss-protection');

        if ($header === null) {
            $this->rating = 'C';
            $this->comment = 'X-XSS-Protection header is not set.';
        } elseif (count($header) > 1) {
            $this->rating = 'C';
            $this->comment = 'X-XSS-Protection header is set multiple times.';
        } else {
            $header = $header[0];

            $this->rating = 'B';
            $this->comment = 'X-XSS-Protection header is set.';

            if (strpos($header, 'mode=block') !== false) {
                $this->rating = 'A';
                $this->comment = "'mode=block' is activated.";
            }

        }
    }

    public static function getDescription()
    {
        // OWASP
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#X-XSS-Protection
        return 'This header enables the Cross-site scripting (XSS) filter in your browser.';
    }

    public static function getBestPractice()
    {
        // OWASP
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#xxp
        return 'X-XSS-Protection: 1; mode=block';
    }
}