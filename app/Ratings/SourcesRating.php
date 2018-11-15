<?php

namespace App\Ratings;

use App\DOMXSSCheck;
use App\HTTPResponse;
use App\TranslateableMessage;

class SourcesRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'SOURCES';
        $this->scoreType = 'info';

        parent::__construct($response);
    }

    protected function rate()
    {
        /**
         * var $html voku\helper\SimpleHtmlDom;.
         */
        $html = $this->getBody();

        if ($html->getIsDOMDocumentCreatedWithoutHtml()) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('NO_CONTENT');
        } else {
            $scriptTags = $html->find('script');

            if (count($scriptTags) == 0) {
                $this->score = 100;
                $this->testDetails->push(TranslateableMessage::get('NO_SCRIPT_TAGS'));
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
                    $this->testDetails->push(TranslateableMessage::get('SOURCES_FOUND', ['AMOUNT' => $sourceCounter]));
                } else {
                    $this->testDetails->push(TranslateableMessage::get('NO_SOURCES_FOUND'));
                }
            }
        }
    }
}
