<?php

namespace App\Ratings;

class HPKPRating extends Rating {

    protected function rate()
    {
        $header = $this->getHeader('public-key-pins');

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

            $beginAge = strpos( $header, 'max-age=' ) + 8;
            $endAge = strpos( $header, ';', $beginAge );
            // if there is no semicolon | max-age=300
            if ( $endAge === false)
                $endAge = strlen($header);

            $maxAge = substr( $header, $beginAge, $endAge - $beginAge );

            if ($maxAge < 1296000) {
                $this->rating   = 'B';
                $this->comment  = __('The keys are pinned for less than 15 days.');
            }
            elseif ($maxAge >= 1296000) {
                $this->rating   = 'A';
                $this->comment  = __('The keys are pinned for more than 15 days.');
            }
            else {
                $this->rating   = 'C';
                $this->comment  = __('An error occured while checking "max-age".');
            }

            if (strpos($header, 'includeSubDomains') !== false) {
                $this->rating   .= '+';
                $this->comment  .= '\n' . __('"includeSubDomains" is set.');
            }

            if (strpos($header, 'report-uri') !== false) {
                $this->rating   .= '+';
                $this->comment  .= '\n' . __('A report-uri is set.');
            }
        }
    }

    public static function getDescription()
    {
        // OWASP Secure Headers Project
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#Public_Key_Pinning_Extension_for_HTTP_.28HPKP.29
        // TODO: Translate
        return 'HTTP Public Key Pinning (HPKP) is a security mechanism which allows HTTPS websites to resist impersonation by attackers using mis-issued or otherwise fraudulent certificates. (For example, sometimes attackers can compromise certificate authorities, and then can mis-issue certificates for a web origin.).';
    }

    public static function getBestPractice()
    {
        // OWASP Secure Headers Prorject
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#hpkp
        return 'pin-sha256="<HASH>"; pin-sha256="<HASH>"; max-age=2592000; includeSubDomains';
    }
}