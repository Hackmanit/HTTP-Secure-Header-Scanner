<?php

namespace App\Ratings;

use App\DOMXSSCheck;
use App\HTTPResponse;
use App\TranslateableMessage;

class SinksRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'SINKS';
        $this->scoreType = 'warning';

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
            $this->score = 100;
            $this->errorMessage = TranslateableMessage::get('NO_CONTENT');
        } else {
            $scriptTags = $html->find('script');

            if (count($scriptTags) == 0) {
                $this->score = 100;
                $this->scoreType = 'success';
                $this->testDetails->push(TranslateableMessage::get('NO_SCRIPT_TAGS'));
            } else {
                $this->score = 100;

                // Search for Sinks and Sources
                $sinkCounter = 0;
                foreach ($scriptTags as $scriptTag) {
                    if ($amountSinks = DOMXSSCheck::hasSinks($scriptTag->innertext, true)) {
                        $sinkCounter += $amountSinks;
                    }
                }

                if ($sinkCounter > 0) {
                    $this->score = 0;
                    $this->testDetails->push(TranslateableMessage::get('SINKS_FOUND', ['AMOUNT' => $sinkCounter]));
                } else {
                    $this->testDetails->push(TranslateableMessage::get('NO_SINKS_FOUND'));
                }

                if ($this->score == 100) {
                    $this->scoreType = 'success';
                }
            }
        }
    }
}
