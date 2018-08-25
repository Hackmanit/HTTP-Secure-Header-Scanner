<?php

namespace App;

class CSPParser
{
    public $headerName = null;
    public $hasLegacyHeader = false;
    public $originalDirectivesString = null;
    public $directives = null;

    /**
     * The CSP header that should be parsed.
     *
     * @param String $header
     */
    function __construct(String $header)
    {
        $this->directives = collect();
        $this->parse($header);
    }

    /**
     * Parse the CSP and set the different parameters to it's values.
     *
     * @param  String $header
     * @return void
     */
    protected function parse(String $header)
    {
        $this->splitHeaderAndDirectives($header);
        $this->createDirectivesCollection();
    }

    /**
     * Returns if 'unsafe-inline' or 'unsafe-eval' are used in the given CSP-Header.
     * @return bool containing unsafe-* values.
     */
    public function containsUnsafeValues()
    {
        return $this->directives->flatten()->contains("'unsafe-inline'") || $this->directives->flatten()->contains("'unsafe-eval'");
    }

    /**
     * @param  String $header
     * @return void
     */
    protected function splitHeaderAndDirectives(String $header)
    {
        // check for header definition
        if (strpos($header, 'Content-Security-Policy:') === 0) {
            $this->headerName = 'Content-Security-Policy';
            $this->originalDirectivesString = substr($header, 25);
        }
        // Check for legacy header
        elseif (strpos($header, 'X-Content-Security-Policy:') === 0) {
            $this->headerName = 'X-Content-Security-Policy';
            $this->originalDirectivesString = substr($header, 27);
            $this->hasLegacyHeader = true;
        }
        // Assume that no header definition is set
        else {
            $this->originalDirectivesString = $header;
        }
    }

    /**
     * Parse the header's directives list to a searchable directives collection.
     *
     * @return void
     */
    protected function createDirectivesCollection()
    {
        // strip leading and trailing whitespace
        $directivesString = trim($this->originalDirectivesString);

        // remove last ; in order to use the explode function without getting an empty value
        if(substr($directivesString, -1, 1) === ";")
            $directivesString = substr($directivesString, 0, -1);

        $splittedDirectives = explode(';', $directivesString);

        foreach ($splittedDirectives as $directive) {
            // Get direcitve name without whitespace
            $directive = trim($directive);

            // Get directives values
            $posWhitespace = strpos($directive, ' ');
            $directiveName = substr($directive, 0, $posWhitespace);
            $directiveValues = trim(substr($directive, $posWhitespace + 1));
            // remove doubled whitespace
            $directiveValues = preg_replace('/\s+/', ' ', $directiveValues);
            // Put the directive with it's values to the directives collection
            $this->directives->put($directiveName, collect(explode(' ', $directiveValues)));
        }
    }

    /**
     * Checks, if the submitted CSP is valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        // only valid directives exist
        if($this->notValidDirectives()->count() == 0) {
            // each directive's values only have valid characters
            foreach ($this->directives as $directive => $values){
                foreach ($values as $value) {
                    if( ! $this->hasOnlyValidCharacters($value)) {
                        return false;
                    }
                }
            }
            return true;
        }

        return false;
    }


    protected function hasOnlyValidCharacters(String $check){
        // Valid chars: (\x09|([\x20-\x2B])|([\x2D-\x3A])|([\x3C-\x7E]))
        // https://www.w3.org/TR/CSP/#framework-directives
        // VARCHAR and whitespace without ',' and ';'
            // Note:
            // Inversing the valid chars does not work with REGEX, but:
            // whitepsace is stripped
            // directives are exploded via ';'
        // Therefore we can search for not printable ASCII-Chars or the ',' in the values list
        if(preg_match('/[^\x21-\x7E]|,|;/', $check) === 0)
            return true;
        return false;
    }

    /**
     * Get a collection of notValidDirectives
     *
     * @return Collection
     */
    public function notValidDirectives() {
        // check if $this->directives KEY is listed on allowed VALUES
        return $this->directives->filter(function ($item, $key){
            return ! $this->getAllowedDirectives()->flatten()->contains($key);
        });
    }

    /**
     * Returns a Collection of allowed directives for the CSP.
     * https://developer.mozilla.org/de/docs/Web/HTTP/Headers/Content-Security-Policy
     *
     * @return Collection allowedDirectives
     */
    protected function getAllowedDirectives() {
        return collect([
            'fetch-directives' => [
                'child-src', // deprecated
                'connect-src',
                'default-src',
                'font-src',
                'frame-src',
                'img-src',
                'manifest-src',
                'media-src',
                'object-src',
                'prefetch-src',
                'script-src',
                'style-src',
                'worker-src'
            ],
            'document-directives' => [
                'base-uri',
                'plugin-types',
                'sandbox',
                'disown-opener' // experimental
            ],
            'navigation-directives' => [
                'form-action',
                'frame-ancestors',
                'navigate-to' // experimental
            ],
            'reporting-directives' => [
                'report-uri', // deprectated
                'report-to'
            ],
            'other-directives' => [
                'block-all-mixed-content',
                'referrer', // deprecated
                'required-sri-for',
                'upgrade-insecure-requests'
            ]
        ]);
    }

}
