<?php

namespace App;

class DomxssCheck
{
    protected $url;
    protected $hasError = false;
    protected $hasSinkError = false;
    protected $sinkErrorMessage = null;
    protected $hasSourceError = false;
    protected $sourceErrorMessage = null;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function hasSources()
    {
        $response = new HTTPResponse($this->url);

        if ($response !== null) {
            // RegEx from original authors
            // https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
            $sourcePattern = "/(location\s*[\[.])|([.\[]\s*[\"']?\s*(arguments|dialogArguments|innerHTML|write(ln)?|open(Dialog)?|showModalDialog|cookie|URL|documentURI|baseURI|referrer|name|opener|parent|top|content|self|frames)\W)|(localStorage|sessionStorage|Database)/";

            $findings = preg_match($sourcePattern, $response->body());

            if ($findings !== false && $findings > 0) {
                return true;
            }

            return false;
        }

        $this->hasSourceError = true;
        $this->sourceErrorMessage = [ 'placeholder' => 'GOT_NO_RESPONSE', 'values' => null ];
        return false;
    }

    public function hasSinks()
    {
        $response = new HTTPResponse($this->url);

        if ($response !== null) {
            // RegEx from original authors
            // https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
            $sourcePattern = "/((src|href|data|location|code|value|action)\s*[\"'\]]*\s*\+?\s*=)|((replace|assign|navigate|getResponseHeader|open(Dialog)?|showModalDialog|eval|evaluate|execCommand|execScript|setTimeout|setInterval)\s*[\"'\]]*\s*\()/";

            $findings = preg_match($sourcePattern, $response->body());

            if ($findings !== false && $findings > 0) {
                return true;
            }

            return false;
        }

        $this->hasSinkError = true;
        $this->sinkErrorMessage = [ 'placeholder' => 'GOT_NO_RESPONSE', 'values' => null ];
        return false;
    }

    public function report() {
        $score = 0;

        if ($this->hasSinks()) $score += 50;
        if ($this->hasSources()) $score += 50;
        
        return [
            'name' => 'DOMXSS',
            'hasError' => $this->hasError,
            'errorMessage' => null,
            'score' => $score,
            'tests' => [
                [
                    'name' => "HAS_SINKS",
                    'hasError' => $this->hasSinkError,
                    'errorMessage' => $this->sinkErrorMessage,
                    'score' => $this->hasSinks() ? 100 : 0,
                    'scoreType' => 'info',
                    'testDetails' => [
                        'placeholder' => $this->hasSinks() ? 'SINKS_FOUND' : 'NO_SINKS_FOUND',
                        'values' => null
                    ]
                ],
                [
                    'name' => "HAS_SOURCES",
                    'hasError' => $this->hasSourceError,
                    'errorMessage' => $this->sourceErrorMessage,
                    'score' => $this->hasSources() ? 100 : 0,
                    'scoreType' => 'info',
                    'testDetails' => [
                        'placeholder' => $this->hasSources() ? 'SOURCES_FOUND' : 'NO_SOURCES_FOUND',
                        'values' => null
                    ]
                ]
            ]
        ];
    }
}
