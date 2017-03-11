<?php

namespace App;

use App\Ratings;

/**
 * Returns a Report / Rating for the given URL.
 */
class Report
{
    public $url;
    public $siteRating = null;
    public $comment = null;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Rate the site's security.
     *
     * TODO: Trigger rate at another place and set it to protected. (constructor does not work if you want it mockable)
     *
     */
    public function rate()
    {
        // Standard is insecure.
        $this->siteRating = 'C';
        $this->comment = __("This site is insecure.");

        /*
         * Criteria for H
         *
         * All Ratings with grade A
         */
        if (
            (strpos($this->getRating("content-security-policy")      , 'A')  !== false) &&
            (strpos($this->getRating("content-type")                , 'A')  !== false) &&
            (strpos($this->getRating("public-key-pins")       , 'A')  !== false) &&
            (strpos($this->getRating("strict-transport-security"), 'A')  !== false) &&
            (strpos($this->getRating("x-content-type-options")        , 'A')  !== false) &&
            (strpos($this->getRating("x-frame-options")              , 'A')  !== false) &&
            (strpos($this->getRating("x-xss-protection")             , 'A')  !== false)
        ) {
            $this->siteRating = 'A++';
            $this->comment = 'WOHA! Great work! Everything is perfect!'; // TODO
        }

        /*
         * Criteria for A
         */
        elseif (
            (strpos($this->getRating("strict-transport-security"), 'A')  !== false) &&
            (strpos($this->getRating("x-xss-protection")             , 'A')  !== false) &&
            ((strpos($this->getRating("content-security-policy"), 'B')  !== false) || (strpos($this->getRating("content-security-policy"), 'A')  !== false)) &&
            (strpos($this->getRating("content-type")                , 'A')  !== false) &&
            (strpos($this->getRating("x-content-type-options")        , 'A')  !== false) &&
            (strpos($this->getRating("x-frame-options")              , 'A')  !== false)
        ) {
            $this->siteRating = 'A';
            $this->comment = __('This site is secure.');

        }

        /*
         * Criteria for B
         */
        elseif (
            ((strpos($this->getRating("strict-transport-security"), 'B')  !== false) || (strpos($this->getRating("strict-transport-security"), 'A')  !== false)) &&
            ((strpos($this->getRating("content-security-policy"), 'B')  !== false) || (strpos($this->getRating("content-security-policy"), 'A')  !== false)) &&
            ((strpos($this->getRating("content-type"), 'B') !== false) || (strpos($this->getRating("content-type"), 'A') !== false)) &&
            (strpos($this->getRating("x-content-type-options"), 'A')  !== false) &&
            (strpos($this->getRating("x-frame-options"), 'A') !== false)
        ) {
            $this->siteRating = 'B';
            $this->comment = __('This site is secure.');
        }

        return $this;
    }

    /**
     * Returns the Report.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get() {
        return collect([
            'url' => $this->url,
            'siteRating' => 'B+', //$this->siteRating,
            'comment' => $this->comment,
            'header' => [
                'Content-Security-Policy' => [
                    'plain' => $this->getHeader('Content-Security-Policy'),
                    'rating' => $this->getRating('Content-Security-Policy'),
                    'comment' => $this->getComment('Content-Security-Policy'),
                    'description' => Ratings\CSPRating::getDescription(),
                    'bestPractice' => Ratings\CSPRating::getBestPractice()
                ],
                'Content-Type' => [
                    'plain' => $this->getHeader('Content-Type'),
                    'rating' => $this->getRating('Content-Type'),
                    'comment' => $this->getComment('Content-Type'),
                    'description' => Ratings\ContentTypeRating::getDescription(),
                    'bestPractice' => Ratings\ContentTypeRating::getBestPractice()
                ],
                'Public-Key-Pins' => [
                    'plain' => $this->getHeader('Public-Key-Pins'),
                    'rating' => $this->getRating('Public-Key-Pins'),
                    'comment' => $this->getComment('Public-Key-Pins'),
                    'description' => Ratings\HPKPRating::getDescription(),
                    'bestPractice' => Ratings\HPKPRating::getBestPractice()
                ],
                'Strict-Transport-Security' => [
                    'plain' => $this->getHeader('Strict-Transport-Security'),
                    'rating' => $this->getRating('Strict-Transport-Security'),
                    'comment' => $this->getComment('Strict-Transport-Security'),
                    'description' => Ratings\HSTSRating::getDescription(),
                    'bestPractice' => Ratings\HSTSRating::getBestPractice()
                ],
                'X-Content-Type-Options' => [
                    'plain' => $this->getHeader('X-Content-Type-Options'),
                    'rating' => $this->getRating('X-Content-Type-Options'),
                    'comment' => $this->getComment('X-Content-Type-Options'),
                    'description' => Ratings\XContentTypeOptionsRating::getDescription(),
                    'bestPractice' => Ratings\XContentTypeOptionsRating::getBestPractice()
                ],
                'X-Frame-Options' => [
                    'plain' => $this->getHeader('X-Frame-Options'),
                    'rating' => $this->getRating('X-Frame-Options'),
                    'comment' => $this->getComment('X-Frame-Options'),
                    'description' => Ratings\XFrameOptionsRating::getDescription(),
                    'bestPractice' => Ratings\XFrameOptionsRating::getBestPractice()
                ],
                'X-Xss-Protection' => [
                    'plain' => $this->getHeader('X-Xss-Protection'),
                    'rating' => $this->getRating('X-Xss-Protection'),
                    'comment' => $this->getComment('X-Xss-Protection'),
                    'description' => Ratings\XXSSProtectionRating::getDescription(),
                    'bestPractice' => Ratings\XXSSProtectionRating::getBestPractice()
                ],
            ]
        ]);
    }

    public function getComment($header)
    {
        $header = strtolower($header);
        switch ($header)
        {
            case "content-security-policy": return (new Ratings\CSPRating($this->url))->getComment(); break;
            case "content-type": return (new Ratings\ContentTypeRating($this->url))->getComment(); break;
            case "public-key-pins": return (new Ratings\HPKPRating($this->url))->getComment(); break;
            case "strict-transport-security": return (new Ratings\HSTSRating($this->url))->getComment(); break;
            case "x-content-type-options": return (new Ratings\XContentTypeOptionsRating($this->url))->getComment(); break;
            case "x-frame-options": return (new Ratings\XFrameOptionsRating($this->url))->getComment(); break;
            case "x-xss-protection": return (new Ratings\XXSSProtectionRating($this->url))->getComment(); break;
        }
    }

    public function getHeader($header)
    {
        $header = strtolower($header);
        switch ($header)
        {
            case "content-security-policy": return (new Ratings\CSPRating($this->url))->getHeader("content-security-policy"); break;
            case "content-type": return (new Ratings\ContentTypeRating($this->url))->getHeader("content-type"); break;
            case "public-key-pins": return (new Ratings\HPKPRating($this->url))->getHeader("public-key-pins"); break;
            case "strict-transport-security": return (new Ratings\HSTSRating($this->url))->getHeader("strict-transport-security"); break;
            case "x-content-type-options": return (new Ratings\XContentTypeOptionsRating($this->url))->getHeader("x-content-type-options"); break;
            case "x-frame-options": return (new Ratings\XFrameOptionsRating($this->url))->getHeader("x-frame-options"); break;
            case "x-xss-protection": return (new Ratings\XXSSProtectionRating($this->url))->getHeader("x-xss-protection"); break;
        }
    }

    public function getRating($header)
    {
        $header = strtolower($header);
        switch ($header)
        {
            case "content-security-policy": return (new Ratings\CSPRating($this->url))->getRating(); break;
            case "content-type": return (new Ratings\ContentTypeRating($this->url))->getRating(); break;
            case "public-key-pins": return (new Ratings\HPKPRating($this->url))->getRating(); break;
            case "strict-transport-security": return (new Ratings\HSTSRating($this->url))->getRating(); break;
            case "x-content-type-options": return (new Ratings\XContentTypeOptionsRating($this->url))->getRating(); break;
            case "x-frame-options": return (new Ratings\XFrameOptionsRating($this->url))->getRating(); break;
            case "x-xss-protection": return (new Ratings\XXSSProtectionRating($this->url))->getRating(); break;
        }
    }

}