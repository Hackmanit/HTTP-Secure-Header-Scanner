<?php

namespace App;

use App\Ratings;
use GuzzleHttp\Client;

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
            (strpos($this->getContentSecurityPolicyRating()      , 'A')  !== false) &&
            (strpos($this->getContentTypeRating()                , 'A')  !== false) &&
            (strpos($this->getHttpPublicKeyPinningRating()       , 'A')  !== false) &&
            (strpos($this->getHttpStrictTransportSecurityRating(), 'A')  !== false) &&
            (strpos($this->getXContentTypeOptionsRating()        , 'A')  !== false) &&
            (strpos($this->getXFrameOptionsRating()              , 'A')  !== false) &&
            (strpos($this->getXXSSProtectionRating()             , 'A')  !== false)
        ) {
            $this->siteRating = 'H';
            $this->comment = 'WOHA! Great work! Everything is perfect!'; // TODO
        }

        /*
         * Criteria for A
         */
        elseif (
            (strpos($this->getHttpStrictTransportSecurityRating(), 'A')  !== false) &&
            (strpos($this->getXXSSProtectionRating()             , 'A')  !== false) &&
            ((strpos($this->getContentSecurityPolicyRating(), 'B')  !== false) || (strpos($this->getContentSecurityPolicyRating(), 'A')  !== false)) &&
            (strpos($this->getContentTypeRating()                , 'A')  !== false) &&
            (strpos($this->getXContentTypeOptionsRating()        , 'A')  !== false) &&
            (strpos($this->getXFrameOptionsRating()              , 'A')  !== false)
        ) {
            $this->siteRating = 'A';
            $this->comment = __('This site is secure.');

        }

        /*
         * Criteria for B
         */
        elseif (
            ((strpos($this->getHttpStrictTransportSecurityRating(), 'B')  !== false) || (strpos($this->getHttpStrictTransportSecurityRating(), 'A')  !== false)) &&
            ((strpos($this->getContentSecurityPolicyRating(), 'B')  !== false) || (strpos($this->getContentSecurityPolicyRating(), 'A')  !== false)) &&
            ((strpos($this->getContentTypeRating(), 'B') !== false) || (strpos($this->getContentTypeRating(), 'A') !== false)) &&
            (strpos($this->getXContentTypeOptionsRating(), 'A')  !== false) &&
            (strpos($this->getXFrameOptionsRating(), 'A') !== false)
        ) {
            $this->siteRating = 'B';
            $this->comment = __('This site is secure.');
        }

        return $this;
    }

    public function __toString()
    {
        return json_encode($this, JSON_PRETTY_PRINT);
    }

    public function getContentSecurityPolicyRating()
    {
        var_dump("CSP Rating triggered");
        return (new Ratings\CSPRating($this->url))->getRating();
    }

    public function getContentTypeRating()
    {
        return (new Ratings\ContentTypeRating($this->url))->getRating();
    }

    public function getHttpPublicKeyPinningRating()
    {
        return (new Ratings\HPKPRating($this->url))->getRating();
    }

    public function getHttpStrictTransportSecurityRating()
    {
        return (new Ratings\HSTSRating($this->url))->getRating();
    }

    public function getXContentTypeOptionsRating()
    {
        return (new Ratings\XContentTypeOptionsRating($this->url))->getRating();
    }

    public function getXFrameOptionsRating()
    {
        return (new Ratings\XFrameOptionsRating($this->url))->getRating();
    }

    public function getXXSSProtectionRating()
    {
        return (new Ratings\XXSSProtectionRating($this->url))->getRating();
    }


}