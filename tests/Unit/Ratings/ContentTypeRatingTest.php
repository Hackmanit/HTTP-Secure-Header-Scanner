<?php

namespace Tests\Unit;

use App\Ratings\ContentTypeRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class ContentTypeRatingTest extends TestCase
{
    /** @test */
    public function contentTypeRating_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);
        $rating = new ContentTypeRating("http://testdomain", $client);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function contentTypeRating_rates_c_when_the_charset_is_missing()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "Content-Type" => "text/html" ]),
        ]);
        $rating = new ContentTypeRating("http://testdomain", $client);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating)->contains("CT_HEADER_WITHOUT_CHARSET"));
    }

    /** @test */
    public function contentTypeRating_rates_c_when_a_wrong_charset_definition_is_given_see_HASEGAWA()
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
            $rating = new ContentTypeRating("http://testdomain", $client);

            $this->assertEquals(0, $rating->score);
            $this->assertTrue(collect($rating)->contains('CT_WRONG_CHARSET'));
        }
    }

    /** @test */
    public function contentTypeRating_rates_a_when_the_charset_is_utf_8()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "Content-Type" => "text/html; charset=utf-8" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=UTF-8" ]),
        ]);

        $rating = new ContentTypeRating("http://testdomain", $client);

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

        $rating = new ContentTypeRating("http://testdomain", $client);

        $this->assertEquals(60, $rating->score);
    }

    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     * @param array $responses
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        return (new Client(["handler" => $handler])) ;
    }
}
