<?php

namespace Tests\Unit;

use App\Ratings\HSTSRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use App\HTTPResponse;

class HSTSRatingTest extends TestCase
{
    /** @test */
    public function hstsRating_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HSTSRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function hstsRating_rates_b_for_a_short_max_age()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=30'
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HSTSRating($response);

        $this->assertEquals(60, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('HSTS_LESS_6'));
    }

    /** @test */
    public function hstsRating_rates_a_for_a_good_max_age()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=' . 6 * 31 * 24 * 60 * 60
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HSTSRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('HSTS_MORE_6'));
    }

    /** @test */
    public function hstsRating_rates_x_plus_for_includeSubDomains()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=30; includeSubDomains'
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HSTSRating($response);

        $this->assertTrue($rating->testDetails->flatten()->contains('INCLUDE_SUBDOMAINS'));
    }

    /** @test */
    public function hstsRating_rates_x_plus_for_preload()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=30; preload'
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HSTSRating($response);

        $this->assertTrue($rating->testDetails->flatten()->contains('HSTS_PRELOAD'));
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
