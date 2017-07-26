<?php

namespace App;

class DomxssReport
{
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function hasSources() {
        $response = new HTTPResponse($this->url);

        if ($response !== null) {
            // RegEx from original authors
            // https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
            $sourcePattern = "/(location\s*[\[.])|([.\[]\s*[\"']?\s*(arguments|dialogArguments|innerHTML|write(ln)?|open(Dialog)?|showModalDialog|cookie|URL|documentURI|baseURI|referrer|name|opener|parent|top|content|self|frames)\W)|(localStorage|sessionStorage|Database)/";

            $findings = preg_match($sourcePattern, $response->body());

            if($findings !== false && $findings > 0)
                return true;
        }

        return false;
    }

    public function hasSinks() {
        $response = new HTTPResponse($this->url);

        if ($response !== null) {
            // RegEx from original authors
            // https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
            $sourcePattern = "/((src|href|data|location|code|value|action)\s*[\"'\]]*\s*\+?\s*=)|((replace|assign|navigate|getResponseHeader|open(Dialog)?|showModalDialog|eval|evaluate|execCommand|execScript|setTimeout|setInterval)\s*[\"'\]]*\s*\()/";

            $findings = preg_match($sourcePattern, $response->body());

            if($findings !== false && $findings > 0)
                return true;
        }

        return false;
    }

}