<?php

namespace Tests\Unit;

use App\Ratings\XContentTypeOptionsRating;
use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class XContentTypeOptionsRatingTest extends TestCase
{

    /** @test */
    public function xContentTypeOptionsRating_rates_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);
        $rating = new XContentTypeOptionsRating("http://testdomain", $client);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function xContentTypeOptionsRating_rates_a_correct_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "X-Content-Type-Options" => "nosniff" ]),
        ]);
        $rating = new XContentTypeOptionsRating("http://testdomain", $client);

        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('XCTO_CORRECT'));
    }

    /** @test */
    public function xContentTypeOptionsRating_rates_a_wrong_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "X-Content-Type-Options" => "wrong entry" ]),
        ]);
        $rating = new XContentTypeOptionsRating("http://testdomain", $client);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('XCTO_NOT_CORRECT'));
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
