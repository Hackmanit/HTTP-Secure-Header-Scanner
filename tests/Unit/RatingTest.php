<?php

namespace Tests\Unit;

use App\Ratings\CSPRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

class RatingTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function csp_rating_is_working()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, ['X-XSS-PROTECTION' => '1; mode=block']),
        ]);

        $rating = new CSPRating("http://testdomain", $client);

        $this->assertEquals("http://testdomain", $rating->url());
    }

    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     * @param array $responses
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses) {
        $mock = new MockHandler( $responses );
        $handler = HandlerStack::create( $mock );
        return (new Client( ["handler" => $handler] )) ;
    }
}
