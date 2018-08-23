<?php

namespace App\Ratings;

use voku\helper\HtmlDomParser;
use GuzzleHttp\Client;
use App\HTTPResponse;
use App\DomxssCheck;

class SinksRating extends Rating
{

    public function __construct(HTTPResponse $response)
    {
        parent::__construct($response);

        $this->name = "SINKS";
        $this->scoreType = "info";
    }

    protected function rate()
    {
        /**
         * var $html voku\helper\SimpleHtmlDom;
         */
        $html = $this->getBody();

        if ($html->size === 0) {
            $this->hasError = true;
            $this->errorMessage = [
                'placeholder' => 'NO_CONTENT',
                'values' => []
            ];

        } else {

            $scriptTags = $html->find('script');

            if (count($scriptTags) == 0) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'NO_SCRIPT_TAGS', 'values' => []]);

            } else {

                $this->score = 100;

                // Search for Sinks and Sources
                $sinkCounter = 0;
                foreach ($scriptTags as $scriptTag) {
                    if ($amountSinks = DOMXSSCheck::hasSinks($scriptTag->innertext, true))
                        $sinkCounter += $amountSinks;
                }

                if ($sinkCounter > 0) {
                    $this->score = 0;
                    $this->testDetails->push([
                        'placeholder' => 'SINKSS_FOUND',
                        'values' => [
                            'AMOUNT' => $sinkCounter
                        ]
                    ]);
                } else {
                    $this->testDetails->push(['placeholder' => 'NO_SINKS_FOUND', 'values' => []]);
                }
            }
        }
    }
}
