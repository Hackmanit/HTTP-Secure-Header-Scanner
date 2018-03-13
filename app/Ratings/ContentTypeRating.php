<?php

namespace App\Ratings;

use voku\helper\HtmlDomParser;
use GuzzleHttp\Client;

class ContentTypeRating extends Rating
{

    public function __construct($url, Client $client = null) {
        parent::__construct($url, $client);

        $this->name = "CONTENT_TYPE";
        $this->scoreType = "warning";
    }

    protected function rate()
    {
        $header = $this->getHeader('content-type');

        if ($header === null) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_NOT_SET";

            $this->checkMetaTag();
        } elseif (count($header) > 1) {
            $this->hasError = true;
            $this->errorMessage = "HEADER_SET_MULTIPLE_TIMES";
            $this->testDetails->push(['placeholder' => 'HEADER', 'values' => [ $header ]]);
        } else {
            $detail = "CT_HEADER_WITHOUT_CHARSET";

            $this->checkMetaTag();

            $header = $header[0];

            $this->testDetails->push(['placeholder' => 'HEADER', 'values' => [$header]]);

            if (stripos($header, 'charset=') !== false) {
                $this->score = 50;
                $detail = "CT_HEADER_WITH_CHARSET";
            }

            if (stripos($header, 'charset=utf-8') !== false) {
                $this->score = 100;
                $detail = "CT_CORRECT";
            }

            // HASEGAWA
            // http://openmya.hacker.jp/hasegawa/public/20071107/s6/h6.html?file=datae.txt
            elseif (
                (stripos($header, 'utf8') !== false) ||
                (stripos($header, 'Windows-31J') !== false) ||
                (stripos($header, 'CP932') !== false) ||
                (stripos($header, 'MS932') !== false) ||
                (stripos($header, 'MS942C') !== false) ||
                (stripos($header, 'sjis') !== false) ||
                (stripos($header, 'jis') !== false)
            ) {
                $this->score = 0;
                $detail = "CT_WRONG_CHARSET";
            }

            $this->testDetails->push(['placeholder' => $detail]);
        }
    }

    protected function checkMetaTag()
    {
        $dom = HtmlDomParser::str_get_html($this->response->body());
        $detailMeta = null; 
        
        // case: <meta charset="utf-8">
        if ($finding = $dom->find('meta[charset]')) {
            $this->score = 30;
            $detailMeta = "CT_META_TAG_SET";

            if (stripos($finding[0]->charset, 'utf-8') !== false) {
                $this->score = 60;
                $detailMeta = "CT_META_TAG_SET_CORRECT";
            }
            
            $this->testDetails->push(['placeholder' => 'META', 'values' => [ $finding[0]->__toString() ]]);
        }
        // case: <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        elseif ($finding = $dom->find('meta[http-equiv=Content-Type]')) {
            if (stripos($finding[0]->content, 'charset=utf-8') !== false) {
                $this->score = 60;
                $detailMeta = "CT_META_TAG_SET_CORRECT";
            } elseif (stripos($finding[0]->content, 'charset=') !== false) {
                $detailMeta = "CT_META_TAG_SET";
                $this->score = 30;
            }

            $this->testDetails->push(['placeholder' => 'META', 'values' => [ $finding[0]->__toString() ]]);
        }

        if ($detailMeta)
            $this->testDetails->push(['placeholder' => $detailMeta]);
    }
}
