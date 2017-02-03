<?php

namespace Tests\Unit;

use App\Ratings\XContentTypeOptionsRating;
use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class XContentTypeOptionsRatingTest extends TestCase
{

    /** @test */
    public function xContentTypeOptionsRating_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200 ),
        ]);
        $rating = new XContentTypeOptionsRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals("The header is not set.", $rating->getComment());
    }

    /** @test */
    public function xContentTypeOptionsRating_rates_a_for_a_correct_header() {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [ "X-Content-Type-Options" => "nosniff" ] ),
        ]);
        $rating = new XContentTypeOptionsRating("http://testdomain", $client);

        $this->assertEquals("A", $rating->getRating());
        $this->assertEquals("The header is set correctly.", $rating->getComment());
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
