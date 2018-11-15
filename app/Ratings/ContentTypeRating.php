<?php

namespace App\Ratings;

use App\HTTPResponse;
use App\TranslateableMessage;
use voku\helper\HtmlDomParser;

class ContentTypeRating extends Rating
{
    public function __construct(HTTPResponse $response)
    {
        $this->name = 'CONTENT_TYPE';
        $this->scoreType = 'warning';

        parent::__construct($response);
    }

    protected function rate()
    {
        $header = $this->getHeader('content-type');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_NOT_SET');

            $this->checkMetaTag();
        } elseif ($header === 'ERROR') {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_ENCODING_ERROR', ['HEADER_NAME' => 'Content-Type']);
        } elseif (is_array($header) && count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = TranslateableMessage::get('HEADER_SET_MULTIPLE_TIMES', ['HEADER' => $header]);
        } else {
            $detail = 'CT_HEADER_WITHOUT_CHARSET';

            $header = $header[0];

            if (stripos($header, 'charset=') !== false) {
                $this->score = 50;
                $detail = 'CT_HEADER_WITH_CHARSET';

                // HASEGAWA
                // http://openmya.hacker.jp/hasegawa/public/20071107/s6/h6.html?file=datae.txt
                if ((stripos($header, 'utf8') !== false) || (stripos($header, 'Windows-31J') !== false) || (stripos($header, 'CP932') !== false) || (stripos($header, 'MS932') !== false) || (stripos($header, 'MS942C') !== false) || (stripos($header, 'sjis') !== false) || (stripos($header, 'jis') !== false)) {
                    $this->score = 0;
                    $detail = 'CT_WRONG_CHARSET';
                }
            }

            if (stripos($header, 'charset=utf-8') !== false) {
                $this->score = 100;
                $detail = 'CT_CORRECT';
            }

            $this->testDetails->push(TranslateableMessage::get($detail, ['HEADER' => $header]));
        }
    }

    protected function checkMetaTag()
    {
        $dom = HtmlDomParser::str_get_html($this->response->body());
        $detailMeta = null;

        // case: <meta charset="utf-8">
        $finding = $dom->find('meta[charset]');
        if (count($finding) > 0) {
            $this->score = 30;
            $detailMeta = 'CT_META_TAG_SET';

            if (stripos($finding[0]->charset, 'utf-8') !== false) {
                $this->score = 60;
                $detailMeta = 'CT_META_TAG_SET_CORRECT';
            }

            $this->testDetails->push(TranslateableMessage::get($detailMeta, ['META' => $finding[0]->__toString()]));
        }

        // case: <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        $finding = $dom->find('meta[http-equiv=Content-Type]');
        if (count($finding)) {
            $detailMeta = 'CT_META_TAG_SET';
            $this->score = 30;

            if (stripos($finding[0]->content, 'charset=utf-8') !== false) {
                $this->score = 60;
                $detailMeta = 'CT_META_TAG_SET_CORRECT';
            }

            $this->testDetails->push(TranslateableMessage::get($detailMeta, ['META' => $finding[0]->__toString()]));
        }
    }
}
