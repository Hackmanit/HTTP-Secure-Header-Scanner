<?php

namespace App;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

class ReportOld {

    protected $scoreBag;
    protected $response;

    public function __construct(GuzzleResponse $response)
    {
        $this->response = $response;
        $this->scoreBag = collect();
        $this
            ->rateXContentTypeOptions()
            ->rateXFrameOptions()
            ->rateXXSSProtection()
            ->rateStrictTransportSecurity()
            ->rateContentSecurityPolicy()
            ;
    }

    // Check Header: X-Content-Type-Options
    protected function rateXContentTypeOptions($required = true)
    {
        if ($this->response->hasHeader('X-Content-Type-Options')) {
            $header = $this->response->getHeader('X-Content-Type-Options');

            // Penalty if the header is set multiple times.
            if ( count($header) > 1 ) {
                $this->scoreBag->push([
                    'name' => 'X-Content-Type-Options',
                    'status' => 'penalty',
                    'score' => -10,
                    'description' => 'The header is set multiple times.'
                ]);
            }

            // Check if, nosniff is set
            if ( strtolower($header[0]) === "nosniff" ) {
                $this->scoreBag->push([
                    'name' => 'X-Content-Type-Options',
                    'status' => 'passed',
                    'score' => 10,
                    'description' => 'Perfect implementation of the header!'
                ]);
            }
            else {
                $this->scoreBag->push([
                    'name' => 'X-Content-Type-Options',
                    'status' => 'penalty',
                    'score' => -3,
                    'description' => 'The header should only have the value "nosniff" if it\'s set'
                ]);
            }

        }
        // has no header
        else {
            $this->scoreBag->push([
                'name' => 'X-Content-Type-Options',
                'status' => 'missing',
                'score' => 0,
                'description' => 'The header is missing.'
            ]);
        }

        return $this;
    }

    // Check Header: X-XSS-Protection
    protected function rateXXSSProtection($required = true)
    {
        if ($this->response->hasHeader('X-XSS-Protection')) {
            $header = $this->response->getHeader('X-XSS-Protection');

            // Penalty if the header is set multiple times.
            if ( count($header) > 1 ) {
                $this->scoreBag->push([
                    'name' => 'X-XSS-Protection',
                    'status' => 'penalty',
                    'score' => -10,
                    'description' => 'The header is set multiple times.'
                ]);
            }

            // Check if "1; mode=block" is set
            if ( strtolower($header[0]) === "1; mode=block" ) {
                $this->scoreBag->push([
                    'name' => 'X-XSS-Protection',
                    'status' => 'passed',
                    'score' => 10,
                    'description' => 'Perfect implementation of the header!'
                ]);
            }
            // Check if "1" is set
            elseif ( strtolower($header[0]) === "1" ) {
                $this->scoreBag->push([
                    'name' => 'X-XSS-Protection',
                    'status' => 'passed',
                    'score' => 1,
                    'description' => 'This is the standard behaviour.'
                ]);
            }
            // Check if the protection is deactivated
            elseif ( strtolower($header[0]) === "0" ) {
                $this->scoreBag->push([
                    'name' => 'X-XSS-Protection',
                    'status' => 'deactivated',
                    'score' => 0,
                    'description' => 'The XSS-Protection in webkit browser is deactivated!'
                ]);
            }
        }
        // has no header
        else {
            $this->scoreBag->push([
                'name' => 'X-XSS-Protection',
                'status' => 'missing',
                'score' => 0,
                'description' => 'The header is missing.'
            ]);
        }

        return $this;
    }

    // Check Header: X-Frame-Options
    protected function rateXFrameOptions($required = true)
    {
        if ( $this->response->hasHeader('X-Frame-Options' )) {
            $header = $this->response->getHeader('X-Frame-Options');

            // Penalty if the header is set multiple times.
            if ( count($header) > 1 ) {
                $this->scoreBag->push([
                    'name' => 'X-Frame-Options',
                    'status' => 'penalty',
                    'score' => -10,
                    'description' => 'The header is set multiple times.'
                ]);
            }

            // Check if "deny" is set
            if ( strtolower($header[0]) === "deny" ) {
                $this->scoreBag->push([
                    'name' => 'X-Frame-Options',
                    'status' => 'passed',
                    'score' => 10,
                    'description' => 'Perfect implementation of the header!'
                ]);
            }
            // Check if "sameorigin" is set
            elseif ( strtolower($header[0]) === "sameorigin" ) {
                $this->scoreBag->push([
                    'name' => 'X-Frame-Options',
                    'status' => 'passed',
                    'score' => 7,
                    'description' => 'It would be more secure if you do not allow framing.'
                ]);
            }
            // Check if any other value is set
            else {
                $this->scoreBag->push([
                    'name' => 'X-Frame-Options',
                    'status' => 'penalty',
                    'score' => -3,
                    'description' => 'The header should only have the value "deny" or "sameorigin" if it\'s set'
                ]);
            }
        }
        // has no header
        else {
            $this->scoreBag->push([
                'name' => 'X-Frame-Options',
                'status' => 'missing',
                'score' => 0,
                'description' => 'The header is missing.'
            ]);
        }

        return $this;
    }

    // Check Header: Strict-Transport-Security
    protected function rateStrictTransportSecurity($required = true)
    {
        if ( $this->response->hasHeader('Strict-Transport-Security') ) {
            $header = $this->response->getHeader('Strict-Transport-Security');


            $this->scoreBag->push([
                'name' => 'Strict-Transport-Security',
                'status' => 'success',
                'score' => 10,
                'description' => 'Only a test'
            ]);

            // Penalty if the header is set multiple times.
            if ( count($header) > 1 ) {
                $this->scoreBag->push([
                    'name' => 'Strict-Transport-Security',
                    'status' => 'penalty',
                    'score' => -10,
                    'description' => 'The header is set multiple times.'
                ]);
            }

            // Check if "max-age=" is set
            if ( $this->extractMaxAge($header[0]) > 0 ) {
                $this->scoreBag->push([
                    'name' => 'Strict-Transport-Security',
                    'status' => 'passed',
                    'score' => 7,
                    'description' => 'Header is set!'
                ]);
            }

            // Check if max-age is under defined $days and give penalty
            $minDays = 30;
            if ( $this->extractMaxAge($header[0]) > $minDays*3600*24 ) {
                $this->scoreBag->push([
                    'name' => 'Strict-Transport-Security',
                    'status' => 'extra',
                    'score' => 3,
                    'description' => 'The value for "max-age" is well chosen!'
                ]);
            }

            // Additional points for 'includeSubdomains'
            if ( strpos($header[0], "includeSubdomains") !== false) {
                $this->scoreBag->push([
                    'name' => 'Strict-Transport-Security',
                    'status' => 'extra',
                    'score' => 4,
                    'description' => 'Perfect, your subdomains are protected!'
                ]);
            }
        }
        // has no header
        else {
            $this->scoreBag->push([
                'name' => 'Strict-Transport-Security',
                'status' => 'missing',
                'score' => 0,
                'description' => 'The header is missing.'
            ]);
        }

        return $this;
    }

    // Check Header: Content-Security-Policy
    protected function rateContentSecurityPolicy($required = true)
    {
        if ( $this->response->hasHeader('Content-Security-Policy') ) {
            $header = $this->response->getHeader('Content-Security-Policy');

            // Penalty if the header is set multiple times.
            if ( count($header) > 1 ) {
                $this->scoreBag->push([
                    'name' => 'Content-Security-Policy',
                    'status' => 'penalty',
                    'score' => -10,
                    'description' => 'The header is set multiple times.'
                ]);
            }

            $csp = $this->getParsedCSPRules($header[0]);


            // RULES from the Google-Paper
            //dd($csp->get('default-src')->hasSelf());



            // Penalty if "unsafe-inline" is used [Paper: CSP Is Dead! Long Live CSP!, Google]
            if ( strpos($header[0], "unsafe-inline") !== false) {
                $this->scoreBag->push([
                    'name' => 'Content-Security-Policy',
                    'status' => 'penalty',
                    'score' => -15,
                    'description' => 'The whole CSP definition is crapped, when you set "unsafe-inline"!'
                ]);
            }
        }
        // has no header
        else {
            $this->scoreBag->push([
                'name' => 'Content-Security-Policy',
                'status' => 'missing',
                'score' => 0,
                'description' => 'The header is missing.'
            ]);
        }

        return $this;
    }

    // Check Meta: ? etc.
    protected function rateMetaStrictTransportSecurity($required = true)
    {
        # code...
    }

    // Helper function to get the overalscore
    public function getTotalScore()
    {
        return $this->scoreBag->sum('score');
    }

    // Helper function to extract the max-age from hash_update_stream
    protected function extractMaxAge($header)
    {
        // max-age can be enquoted, so just strip them out
        $header = $this->stripQuotes($header);

        // no additional flags are set
        $maxAge = substr($header, strlen('max-age='));

        // remove additional flags if they are set
        if (strpos($maxAge, ';') !== false) {
            $maxAge = substr($maxAge, 0, strpos($maxAge, ';'));
        }

        return (int) $maxAge;
    }

    // Helper function to strip out possible quptes
    protected function stripQuotes($string)
    {
        str_replace('"', "", $string);
        str_replace("'", "", $string);

        return $string;
    }


    // Returns a collection with each CSP parameters
    protected function getParsedCSPRules($header)
    {
        // Split CSP in array elements, CSP elements are seperated by ";"
        $rules = collect( explode(";", $header) );
        $rulesParsed = collect();

        foreach( $rules as $key => $item) {
            $key = substr($item, 0, strpos($item, ' '));
            $item = substr($item, strpos($item, ' ') + 1);
            $rulesParsed->put($key, new CSPRule($key, $item));
        }

        return $rulesParsed;
    }

    public function toJson() {
        return $this->scoreBag->groupBy('name');
    }
}

class CSPRule {

    protected $parameter;
    protected $rule;
    protected $urls;

    public function __construct($parameter, $rule)
    {
        $this->parameter = $parameter;
        $this->rule = $rule;
    }

    // Verifies if 'self' or "self" is set.
    public function hasSelf()
    {
        if ( (strpos($this->rule, "'self'") !== false) || (strrpos($this->rule, '"self"') !== false) )
            return true;
        return false;
    }

    // Verifies if 'none' or "none" is set.
    public function hasNone()
    {
        if ( (strpos($this->rule, "'none'") !== false) || (strrpos($this->rule, '"none"') !== false) )
            return true;
        return false;
    }

    // Verifies if any wildcard is included
    public function hasWildcards()
    {
        if ( strpos($this->rule, "*") !== false)
            return true;
        return false;
    }

    // Verifies if "unsafe-inline" is used
    public function hasUnsafeInline()
    {
        if ( strpos($this->rule, "unsafe-inline") !== false)
            return true;
        return false;
    }

    // Verifies is nonces are used.
    public function hasNonce()
    {
        if ( strpos($this->rule, "nonce-") !== false)
            return true;
        return false;
    }

    // Verifies if hash values are used.
    public function hasHash()
    {
        if ( (strpos($this->rule, "sha256-") !== false)
            || (strpos($this->rule, "sha384-") !== false)
            || (strpos($this->rule, "sha-512-") !== false) )
            return true;
        return false;
    }


}
