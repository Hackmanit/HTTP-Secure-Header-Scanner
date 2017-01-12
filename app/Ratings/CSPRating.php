<?php

namespace App\Ratings;

use App\HTTPResponse;

class CSPRating implements Rating
{

    protected $url;
    protected $rating;
    protected $comment;

    public function __construct($url)
    {
        $this->url = $url;
        $this->rate();
    }

    protected function rate()
    {
        $header = $this->getHeader();

        if ($header === null) {
            $this->rating   = 'C';
            $this->comment  = 'Content-Security-Policy header is not set.';
        }

        elseif (count($header) > 1) {
            $this->rating   = 'C';
            $this->comment  = 'Content-Security-Policy header is set multiple times.';
        }

        else {
            $header = $header[0];

            if (strpos($header, 'unsafe-inline') !== false && strpos($header, 'unsafe-eval') !== false) {
                $this->rating   = 'C';
                $this->comment  = 'Header contains "unsafe-inline" or "unsafe-eval" directives.';
            }
            elseif (strpos($header, 'unsafe-inline') === false && strpos($header, 'unsafe-eval') === false && strpos($header, "default-src 'none'") === false) {
                $this->rating   = 'B';
                $this->comment  = 'Header is "unsafe-" free.';
            }
            elseif (strpos($header, 'unsafe-inline') === false && strpos($header, 'unsafe-eval') === false && strpos($header, "default-src 'none'") !== false) {
                $this->rating   = 'A';
                $this->comment  = "Header is 'unsafe-' free and includes default-src 'none'";
            }
        }

        // Check if legacy header is available
        if (HTTPResponse::get($this->url)->getHeaders()->get("X-Content-Security-Policy") !== null) {
            $this->comment .= '\n' . 'The legacy header X-Content-Security-Policy (that is only used for IE11 with CSP v.1) is set. The new and standardized header is Content-Security-Policy.';
        }
    }


    public static function getDescription()
    {
        // OWASP Secure Headers Project
        // https://www.owasp.org/index.php/OWASP_Secure_Headers_Project#Content-Security-Policy
        return 'Content Security Policy (CSP) requires careful tuning and precise definition of the policy. If enabled, CSP has significant impact on the way browser renders pages (e.g., inline JavaScript disabled by default and must be explicitly allowed in policy). CSP prevents a wide range of attacks, including Cross-site scripting and other cross-site injections.';
    }

    public static function getBestPractice()
    {
        // Mozilla Observatory
        // https://github.com/mozilla/http-observatory/blob/master/httpobs/docs/scoring.md
        return "Best Practice is to use the CSP with default-src 'none' and without any 'unsafe-eval' or 'unsafe-inline'";
    }

    public function getHeader()
    {
        return HTTPResponse::get($this->url)->getHeaders()->get("Content-Security-Policy");
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
