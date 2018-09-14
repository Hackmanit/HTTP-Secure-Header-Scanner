<?php

namespace Tests\Unit;

use App\HTTPResponse;
use App\Ratings\XFrameOptionsRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class XFrameOptionsRatingTest extends TestCase
{
    /** @test */
    public function xFrameOptionsRating_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XFrameOptionsRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function xFrameOptionsRating_rates_c_when_wildcards_are_used()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'X-Frame-Options' => 'allow-from *',
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XFrameOptionsRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('XFO_WILDCARDS'));
    }

    /** @test */
    public function xFrameOptionsRating_rates_a_when_set_and_no_wildcards_are_used()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'X-Frame-Options' => 'deny',
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XFrameOptionsRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('XFO_CORRECT'));
    }

    /** @test */
    public function XFrameOptionsRating_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ['X-Frame-Options' => zlib_encode('SGVsbG8gV29ybGQ=', ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XFrameOptionsRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('HEADER_ENCODING_ERROR'));
    }

    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     *
     * @param array $responses
     *
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        return new Client(['handler' => $handler]);
    }
}
