<?php

namespace App\Ratings;

use App\DOMXSSCheck;
use App\HTTPResponse;

class SourcesRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        parent::__construct($response);

        $this->name = 'SOURCES';
        $this->scoreType = 'info';
    }

    protected function rate()
    {
        /**
         * var $html voku\helper\SimpleHtmlDom;.
         */
        $html = $this->getBody();

        if ($html->getIsDOMDocumentCreatedWithoutHtml()) {
            $this->hasError = true;
            $this->errorMessage = [
                'placeholder' => 'NO_CONTENT',
                'values'      => [],
            ];
        } else {
            $scriptTags = $html->find('script');

            if (count($scriptTags) == 0) {
                $this->score = 100;
                $this->testDetails->push(['placeholder' => 'NO_SCRIPT_TAGS', 'values' => []]);
            } else {
                $this->score = 100;

                // Search for Sinks and Sources
                $sourceCounter = 0;
                foreach ($scriptTags as $scriptTag) {
                    if ($amountSources = DOMXSSCheck::hasSources($scriptTag->innertext, true)) {
                        $sourceCounter += $amountSources;
                    }
                }

                if ($sourceCounter > 0) {
                    $this->score = 0;
                    $this->testDetails->push([
                        'placeholder' => 'SOURCES_FOUND',
                        'values'      => [
                            'AMOUNT' => $sourceCounter,
                        ],
                    ]);
                } else {
                    $this->testDetails->push(['placeholder' => 'NO_SOURCES_FOUND', 'values' => []]);
                }
            }
        }
    }
}
