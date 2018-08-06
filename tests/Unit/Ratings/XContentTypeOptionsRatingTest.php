<?php

namespace Tests\Unit;

use App\Ratings\XContentTypeOptionsRating;
use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use App\HTTPResponse;

class XContentTypeOptionsRatingTest extends TestCase
{

    /** @test */
    public function xContentTypeOptionsRating_rates_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XContentTypeOptionsRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function xContentTypeOptionsRating_rates_a_correct_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "X-Content-Type-Options" => "nosniff" ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XContentTypeOptionsRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('XCTO_CORRECT'));
    }

    /** @test */
    public function xContentTypeOptionsRating_rates_a_wrong_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "X-Content-Type-Options" => "wrong entry" ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XContentTypeOptionsRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('XCTO_NOT_CORRECT'));
    }

    /** @test */
    public function xContentTypeOptionsRating_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ["X-Content-Type-Options" => zlib_encode("SGVsbG8gV29ybGQ=", ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XContentTypeOptionsRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('HEADER_ENCODING_ERROR'));
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
