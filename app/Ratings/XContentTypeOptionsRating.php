<?php

namespace App\Ratings;

use App\HTTPResponse;

class XContentTypeOptionsRating extends Rating
{

    protected function rate()
    {
        $header = $this->getHeader();

        if ($header === null) {
            $this->rating = 'C';
            $this->comment = 'X-Content-Type-Options header is not set.';
        } elseif (count($header) > 1) {
            $this->rating = 'C';
            $this->comment = 'X-Content-Type-Options header is set multiple times.';
        } else {
            $header = $header[0];

            $this->rating = 'C';
            $this->comment = 'X-Content-Type-Options header is not set correctly.';

            if (strpos($header, 'nosniff') !== false) {
                $this->rating = 'A';
                $this->comment = "X-Content-Type-Options is set.";
            }

        }
    }

    public static function getDescription()
    {
        // OWASP
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#X-Content-Type-Options
        return 'Setting this header will prevent the browser from interpreting files as something else than declared by the content type in the HTTP headers.';
    }

    public static function getBestPractice()
    {
        // OWASP
        https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#xcto
        return 'X-Content-Type-Options: nosniff';
    }

    public function getHeader()
    {
        return HTTPResponse::get($this->url)->getHeaders()->get("X-Content-Type-Options");
    }

}