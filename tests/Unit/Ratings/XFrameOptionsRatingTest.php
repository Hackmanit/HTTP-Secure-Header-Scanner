<?php

namespace Tests\Unit;

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
        $rating = new XFrameOptionsRating("http://testdomain", $client);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function xFrameOptionsRating_rates_c_when_wildcards_are_used()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                "X-Frame-Options" => "allow-from *"
            ]),
        ]);
        $rating = new XFrameOptionsRating("http://testdomain", $client);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('XFO_WILDCARDS'));
    }

    /** @test */
    public function xFrameOptionsRating_rates_a_when_set_and_no_wildcards_are_used()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                "X-Frame-Options" => "deny"
            ]),
        ]);
        $rating = new XFrameOptionsRating("http://testdomain", $client);

        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('XFO_CORRECT'));
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
