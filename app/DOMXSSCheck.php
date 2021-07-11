<?php

namespace App;

use App\Http\Requests\ScanStartRequest;
use App\Ratings\SinksRating;
use App\Ratings\SourcesRating;

class DOMXSSCheck
{
    protected $response = null;

    public function __construct(ScanStartRequest $request)
    {
        $this->response = new HTTPResponse($request);
    }

    public function report()
    {
        if ($this->response->hasErrors()) {
            return [
                'name'         => 'DOMXSS',
                'version' 	    => file(base_path('VERSION'), FILE_IGNORE_NEW_LINES)[0],
                'hasError'     => true,
                'errorMessage' => TranslateableMessage::get('NO_HTTP_RESPONSE'),
                'score'        => 0,
                'tests'        => [],
            ];
        }

        $sourcesRating = new SourcesRating($this->response);
        $sinksRating = new SinksRating($this->response);

        return [
            'name'         => 'DOMXSS',
            'version' 	    => file(base_path('VERSION'), FILE_IGNORE_NEW_LINES)[0],
            'hasError'     => false,
            'errorMessage' => null,
            'score'        => ($sourcesRating->score + $sinksRating->score) / 2,
            'tests'        => [
                $sourcesRating,
                $sinksRating,
            ],
        ];
    }

    public static function hasSources(String $input, bool $AMOUNT = false)
    {
        // RegEx from original authors
        // https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
        $sourcePattern = '/(location\s*[\[.])|([.\[]\s*[\"\']?\s*(arguments|dialogArguments|innerHTML|write(ln)?|open(Dialog)?|showModalDialog|cookie|URL|documentURI|baseURI|referrer|name|opener|parent|top|content|self|frames)\W)|(localStorage|sessionStorage|Database)/';
        $findings = preg_match_all($sourcePattern, $input);

        if ($AMOUNT) {
            return $findings;
        }

        return $findings ? true : false;
    }

    public static function hasSinks(String $input, bool $AMOUNT = false)
    {
        // RegEx from original authors
        // https://github.com/wisec/domxsswiki/wiki/Finding-DOMXSS
        $sinksPattern = '/((src|href|data|location|code|value|action)\s*[\"\'\]]*\s*\+?\s*=)|((replace|assign|navigate|getResponseHeader|open(Dialog)?|showModalDialog|eval|evaluate|execCommand|execScript|setTimeout|setInterval)\s*[\"\'\]]*\s*\()/';
        $findings = preg_match_all($sinksPattern, $input);

        $sinksPattern = '/after\(|\.append\(|\.before\(|\.html\(|\.prepend\(|\.replaceWith\(|\.wrap\(|\.wrapAll\(|\$\(|\.globalEval\(|\.add\(|jQUery\(|\$\(|\.parseHTML\(/';
        $findings += preg_match_all($sinksPattern, $input);

        if ($AMOUNT) {
            return $findings;
        }

        return $findings ? true : false;
    }
}
