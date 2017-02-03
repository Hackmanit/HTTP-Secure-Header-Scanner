<?php

namespace Tests\Unit;

use App\Ratings\XXSSProtectionRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class XXSSProtectionRatingTest extends TestCase
{

    /** @test */
    public function xXSSProtection_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200 ),
        ]);
        $rating = new XXSSProtectionRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals("The header is not set.", $rating->getComment());
    }

    /** @test */
    public function xXSSProtection_rates_b_for_a_set_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [ "X-Xss-Protection" => "0"] ),
            new Response( 200, [ "X-Xss-Protection" => "1"] ),
        ]);

        $rating = new XXSSProtectionRating("http://testdomain", $client);

        $this->assertEquals("B", $rating->getRating());
        $this->assertEquals("The header is set correctly.", $rating->getComment());

        $rating = new XXSSProtectionRating("http://testdomain", $client);

        $this->assertEquals("B", $rating->getRating());
        $this->assertEquals("The header is set correctly.", $rating->getComment());
    }

    /** @test */
    public function xXSSProtection_rates_a_for_mode_block()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [ "X-Xss-Protection" => "1; mode=block"] ),
        ]);

        $rating = new XXSSProtectionRating("http://testdomain", $client);

        $this->assertEquals("A", $rating->getRating());
        $this->assertEquals("The header is set correctly.\n\"mode=block\" is activated.", $rating->getComment());
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
