<?php

namespace Tests\Unit;

use App\Ratings\XXSSProtectionRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use App\HTTPResponse;

class XXSSProtectionRatingTest extends TestCase
{

    /** @test */
    public function xXSSProtection_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]); 
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function xXSSProtection_rates_a_set_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "X-Xss-Protection" => "0"]),
            new Response(200, [ "X-Xss-Protection" => "1"]),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(50, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('XXSS_CORRECT'));

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(50, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('XXSS_CORRECT'));
    }

    /** @test */
    public function xXSSProtection_rates_mode_block()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [ "X-Xss-Protection" => "1; mode=block"]),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating)->flatten()->contains('XXSS_BLOCK'));
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
