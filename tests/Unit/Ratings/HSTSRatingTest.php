<?php

namespace Tests\Unit;

use App\Ratings\HSTSRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class HSTSRatingTest extends TestCase
{
    /** @test */
    public function hstsRating_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);
        $rating = new HSTSRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals("The header is not set.", $rating->getComment());
    }

    /** @test */
    public function hstsRating_rates_b_for_a_short_max_age()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=30'
            ]),
        ]);
        $rating = new HSTSRating("http://testdomain", $client);

        $this->assertEquals("B", $rating->getRating());
        $this->assertEquals('The value for "max-age" is smaller than 6 months.', $rating->getComment());
    }

    /** @test */
    public function hstsRating_rates_a_for_a_good_max_age()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=' . 6 * 31 * 24 * 60 * 60
            ]),
        ]);
        $rating = new HSTSRating("http://testdomain", $client);

        $this->assertEquals("A", $rating->getRating());
        $this->assertEquals('The value for "max-age" is greater than 6 months.', $rating->getComment());
    }

    /** @test */
    public function hstsRating_rates_x_plus_for_includeSubDomains()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=30; includeSubDomains'
            ]),
        ]);
        $rating = new HSTSRating("http://testdomain", $client);

        $this->assertStringEndsWith("+", $rating->getRating());
        $this->assertStringEndsWith('"includeSubDomains" is set.', $rating->getComment());
    }

    /** @test */
    public function hstsRating_rates_x_plus_for_preload()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Strict-Transport-Security' => 'max-age=30; preload'
            ]),
        ]);
        $rating = new HSTSRating("http://testdomain", $client);

        $this->assertStringEndsWith("+", $rating->getRating());
        $this->assertStringEndsWith('"preload" is set.', $rating->getComment());
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
