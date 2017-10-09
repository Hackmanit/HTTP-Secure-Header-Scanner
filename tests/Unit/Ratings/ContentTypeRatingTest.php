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

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals("The header is not set.", $rating->getComment());
    }

    /** @test */
    public function contentTypeRating_rates_c_when_the_charset_is_missing()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "Content-Type" => "text/html" ]),
        ]);
        $rating = new ContentTypeRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals("The header is set without the charset.", $rating->getComment());
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

            $this->assertEquals("C", $rating->getRating());
            $this->assertStringStartsWith("The given charset is wrong and thereby ineffective.", $rating->getComment());
        }
    }

    /** @test */
    public function contentTypeRating_rates_a_when_the_charset_is_utf_8()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "Content-Type" => "text/html; charset=utf-8" ]),
            new Response(200, [ "Content-Type" => "text/html; charset=UTF-8" ]),
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $rating = new ContentTypeRating("http://testdomain", $client);

            $this->assertEquals("A", $rating->getRating());
            $this->assertEquals("The header is set with the charset and follows the best practice.", $rating->getComment());
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

        $this->assertEquals("B", $rating->getRating());
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
