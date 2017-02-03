<?php

namespace App\Ratings;

class XXSSProtectionRating extends Rating
{

    protected function rate()
    {
        $header = $this->getHeader('x-xss-protection');

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

            $this->rating = 'B';
            $this->comment = __('The header is set correctly.');

            if (strpos($header, 'mode=block') !== false) {
                $this->rating = 'A';
                $this->comment .= "\n" . __('"mode=block" is activated.');
            }

        }
    }

    public static function getDescription()
    {
        // OWASP
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#X-XSS-Protection
        // TODO: Translate
        return 'This header enables the Cross-site scripting (XSS) filter in your browser.';
    }

    public static function getBestPractice()
    {
        // OWASP
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#xxp
        return 'X-XSS-Protection: 1; mode=block';
    }
}