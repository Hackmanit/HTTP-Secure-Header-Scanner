<?php

namespace App;

use App\Ratings;

/**
 * Returns a Report / Rating for the given URL.
 *
 * @property Ratings\ContentTypeRating ContentTypeRating
 * @property Ratings\CSPRating ContentSecurityPolicyRating
 * @property Ratings\HPKPRating HttpPublicKeyPinningRating
 * @property Ratings\HSTSRating HttpStrictTransportSecurityRating
 * @property Ratings\XContentTypeOptionsRating XContentTypeOptionsRating
 * @property Ratings\XFrameOptionsRating XFrameOptionsRating
 * @property Ratings\XXSSProtectionRating XXSSProtectionRating
 */
class Report
{

    public $url = null;
    public $status = 'error';
    public $siteRating = 'C';
    public $comment = '';

    public function __construct($url)
    {
        $this->url = $url;

        if ((new HTTPResponse($this->url))->get() === null) {
            $this->status = "error";
        }
        else {
            $this->doRatings();
            $this->doSiteRating();
            $this->status = 'success';
        }
    }

    /**
     * Rate the specific headers.
     */
    protected function doRatings()
    {
        $this->ContentSecurityPolicyRating       = new Ratings\CSPRating( $this->url );
        $this->ContentTypeRating                 = new Ratings\ContentTypeRating( $this->url );
        $this->HttpPublicKeyPinningRating        = new Ratings\HPKPRating( $this->url );
        $this->HttpStrictTransportSecurityRating = new Ratings\HSTSRating( $this->url );
        $this->XContentTypeOptionsRating         = new Ratings\XContentTypeOptionsRating( $this->url );
        $this->XFrameOptionsRating               = new Ratings\XFrameOptionsRating( $this->url );
        $this->XXSSProtectionRating              = new Ratings\XXSSProtectionRating( $this->url );
    }

    /**
     * Rate the site's security.
     */
    protected function doSiteRating()
    {
        $this->siteRating = 'C';
        $this->comment = __('This site is insecure.');

        /*
         * Criteria for H
         *
         * All Ratings with grade A
         */
        if (
            (strpos($this->ContentSecurityPolicyRating->getRating()      , 'A')  !== false) &&
            (strpos($this->ContentTypeRating->getRating()                , 'A')  !== false) &&
            (strpos($this->HttpPublicKeyPinningRating->getRating()       , 'A')  !== false) &&
            (strpos($this->HttpStrictTransportSecurityRating->getRating(), 'A')  !== false) &&
            (strpos($this->XContentTypeOptionsRating->getRating()        , 'A')  !== false) &&
            (strpos($this->XFrameOptionsRating->getRating()              , 'A')  !== false) &&
            (strpos($this->XXSSProtectionRating->getRating()             , 'A')  !== false)
        ) {
            $this->siteRating = 'H';
            $this->comment = 'WOHA! Great work! Everything is perfect!'; // TODO
        }

        /*
         * Criteria for A
         */
        elseif (
            (strpos($this->HttpStrictTransportSecurityRating->getRating(), 'A')  !== false) &&
            (strpos($this->XXSSProtectionRating->getRating()             , 'A')  !== false) &&
            ((strpos($this->ContentSecurityPolicyRating->getRating(), 'B')  !== false) || (strpos($this->ContentSecurityPolicyRating->getRating(), 'A')  !== false)) &&
            (strpos($this->ContentTypeRating->getRating()                , 'A')  !== false) &&
            (strpos($this->XContentTypeOptionsRating->getRating()        , 'A')  !== false) &&
            (strpos($this->XFrameOptionsRating->getRating()              , 'A')  !== false)
        ) {
            $this->siteRating = 'A';
            $this->comment = __('This site is secure.');

        }

        /*
         * Criteria for B
         */
        elseif (
            ((strpos($this->HttpStrictTransportSecurityRating->getRating(), 'B')  !== false) || (strpos($this->HttpStrictTransportSecurityRating->getRating(), 'A')  !== false)) &&
            ((strpos($this->ContentSecurityPolicyRating->getRating(), 'B')  !== false) || (strpos($this->ContentSecurityPolicyRating->getRating(), 'A')  !== false)) &&
            ((strpos($this->ContentTypeRating->getRating(), 'B') !== false) || (strpos($this->ContentTypeRating->getRating(), 'A') !== false)) &&
            (strpos($this->XContentTypeOptionsRating->getRating(), 'A')  !== false) &&
            (strpos($this->XFrameOptionsRating->getRating(), 'A') !== false)
        ) {
            $this->siteRating = 'B';
            $this->comment = __('This site is secure.');
        }
    }

    public function __toString()
    {
        return json_encode($this, JSON_PRETTY_PRINT);
    }
}