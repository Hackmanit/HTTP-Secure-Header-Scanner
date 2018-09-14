<?php

namespace Tests\Unit;

use App\Ratings\ContentTypeRating;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use App\HTTPResponse;

class ContentTypeRatingTest extends TestCase
{
    /** @test */
    public function contentTypeRating_rates_0_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function contentTypeRating_rates_0_when_the_charset_is_missing()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "Content-Type" => "text/html" ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating)->contains("CT_HEADER_WITHOUT_CHARSET"));
    }

    /** @test */
    public function contentTypeRating_rates_0_when_a_wrong_charset_definition_is_given_see_HASEGAWA()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "Content-Type" => "text/html; charset=utf8" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=Windows-31J" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=CP932" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=MS932" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=MS942C" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=sjis" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=jis" ]),
        ]);

        for ($i = 1; $i <= 7; $i++) {
            $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

            $this->assertEquals(0, $rating->score);
            $this->assertTrue(collect($rating)->contains('CT_WRONG_CHARSET'));
        }
    }

    /** @test */
    public function contentTypeRating_rates_100_when_the_charset_is_utf_8()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "Content-Type" => "text/html; charset=utf-8" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=UTF-8" ]),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        for ($i = 1; $i <= 2; $i++) {
            $this->assertEquals(100, $rating->score);
        }
    }

    /** @test */
    public function if_the_header_is_not_set_the_meta_tag_is_rated()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/example.org.html");

        $client = $this->getMockedGuzzleClient([
            new Response(200, [ ], $sampleBody)
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(60, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CT_META_TAG_SET_CORRECT'));
    }

    /** @test */
    public function if_the_header_is_set_the_meta_tag_is_not_rated()
    {
        $sampleBody = file_get_contents(base_path() . "/tests/Unit/example.org.html");

        $client = $this->getMockedGuzzleClient([
            new Response(200, ["Content-Type" => "text/html; charset=utf-8"], $sampleBody),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertFalse(collect($rating->testDetails)->flatten()->contains('CT_META_TAG_SET_CORRECT'));
    }

    /** @test */
    public function ContentTypeRating_rates_30_if_only_the_meta_tag_is_set_but_without_an_charset() {
        $sampleBody = '
            <html><head><meta http-equiv="Content-Type" content="text/html" /></head><body></body></html>
        ';

        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(30, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CT_META_TAG_SET'));
    }

    /** @test */
    public function ContentTypeRating_rates_60_if_only_the_meta_tag_is_set_but_with_the_correct_charset()
    {
        $sampleBody = '
            <html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body></body></html>
        ';

        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(60, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CT_META_TAG_SET_CORRECT'));
    }

    /** @test */
    public function ContentTypeRating_rates_30_if_only_the_short_meta_tag_is_set_but_with_another_charset()
    {
        $sampleBody = '
            <html><head><meta charset="ISO-8859-1" /></head><body></body></html>
        ';

        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(30, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CT_META_TAG_SET'));
    }

    /** @test */
    public function ContentTypeRating_rates_60_if_only_the_short_meta_tag_is_set_but_with_the_correct_charset()
    {
        $sampleBody = '
            <html><head><meta charset="UTF-8" /></head><body></body></html>
        ';

        $client = $this->getMockedGuzzleClient([
            new Response(200, [], $sampleBody),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(60, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('CT_META_TAG_SET_CORRECT'));
    }

    /** @test */
    public function ContentTypeRating_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ["Content-Type" => zlib_encode("SGVsbG8gV29ybGQ=", ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ContentTypeRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('HEADER_ENCODING_ERROR'));
    }

}
