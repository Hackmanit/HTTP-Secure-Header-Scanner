<?php

namespace App;

use App\Ratings\ContentTypeRating;
use App\Ratings\CSPRating;
use App\Ratings\HPKPRating;
use App\Ratings\HSTSRating;
use App\Ratings\ReferrerPolicyRating;
use App\Ratings\XContentTypeOptionsRating;
use App\Ratings\XFrameOptionsRating;
use App\Ratings\XXSSProtectionRating;
use GuzzleHttp\Client;

/**
 * Returns a HeaderReport / Rating for the given URL.
 */
class HeaderCheck
{
    protected $response = null;

    public function __construct($url, Client $client = null)
    {
        $this->response = new HTTPResponse($url, $client);
    }

    public function report()
    {
        if ($this->response->hasErrors()) {
            return [
                'name'         => 'HEADER',
                'version'      => file(base_path('VERSION'), FILE_IGNORE_NEW_LINES)[0],
                'hasError'     => true,
                'errorMessage' => TranslateableMessage::get('NO_HTTP_RESPONSE'),
                'score'        => 0,
                'tests'        => [],
            ];
        }

        $ratings = collect([
            new CSPRating($this->response),
            new ContentTypeRating($this->response),
            new HPKPRating($this->response),
            new ReferrerPolicyRating($this->response),
            new HSTSRating($this->response),
            new XContentTypeOptionsRating($this->response),
            new XFrameOptionsRating($this->response),
            new XXSSProtectionRating($this->response),
        ]);

        // Calculating score as an average of the single scores WITHOUT `scoreType = 'bonus'` Ratings.
        $score = 0;
        $scoredRatings = 0;
        foreach ($ratings as $rating) {
            if ($rating->scoreType === 'bonus') {
                continue;
            }
            $score += $rating->score;
            $scoredRatings++;
        }

        $score = floor($score / $scoredRatings);

        return [
            'name'         => 'HEADER',
            'version'      => file(base_path('VERSION'), FILE_IGNORE_NEW_LINES)[0],
            'hasError'     => $ratings->whereIn('scoreType', ['warning'])->contains('hasError', true),
            'errorMessage' => null,
            'score'        => $score,
            'tests'        => $ratings,
        ];
    }
}
