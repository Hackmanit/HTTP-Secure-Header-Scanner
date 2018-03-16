<?php

namespace App;

use App\Ratings\CSPRating;
use App\Ratings\ContentTypeRating;
use App\Ratings\HPKPRating;
use App\Ratings\HSTSRating;
use App\Ratings\XContentTypeOptionsRating;
use App\Ratings\XFrameOptionsRating;
use App\Ratings\XXSSProtectionRating;


/**
 * Returns a HeaderReport / Rating for the given URL.
 */
class HeaderCheck
{
    protected $response = null;

    public function __construct($url)
    {
        $this->response = new HTTPResponse($url);
    }

   
    public function report()
    {
        if($this->response->hasErrors()){
            return [
                'name' => 'HEADER',
                'hasError' => true,
                'errorMessage' => 'NO_HTTP_RESPONSE',
                'score' => 0,
                'tests' => []
            ];
        }

        $cspRating = new CSPRating($this->response);
        $contentTypeRating = new ContentTypeRating($this->response);
        $hpkpRating = new HPKPRating($this->response);
        $hstsRating = new HSTSRating($this->response);
        $xContenTypeOptionsRating = new XContentTypeOptionsRating($this->response);
        $xFrameOptionsRating = new XFrameOptionsRating($this->response);
        $xXssProtectionRating = new XXSSProtectionRating($this->response);


        // Calculating score as an average of the single scores WITHOUT the HPKP scan
        $score = 0;
        $score+= $cspRating->score;
        $score+= $contentTypeRating->score;
        $score+= $hstsRating->score;
        $score+= $xContenTypeOptionsRating->score;
        $score+= $xFrameOptionsRating->score;
        $score+= $xXssProtectionRating->score;
        $score = floor($score / 6);

        return [
            'name' => 'HEADER',
            'hasError' => false,
            'errorMessage' => null,
            'score' => $score,
            'tests' => [
                $cspRating,
                $contentTypeRating,
                $hpkpRating,
                $hstsRating,
                $xContenTypeOptionsRating,
                $xFrameOptionsRating,
                $xXssProtectionRating,
            ]
        ];
    }
}
