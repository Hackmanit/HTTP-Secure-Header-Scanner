<?php

namespace Tests\Unit;

use App\Ratings\CSPRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Tests\TestCase;

/**
 * CSPRating is not good. There are many ways to bypass this "secure" rating.
 * TODO: Improve parsing and rating of CSP.
 *
 * Class CSPRatingTest
 * @package Tests\Unit
 */
class CSPRatingTest extends TestCase
{
    /** @test */
    public function cspRating_rates_c_because_header_is_not_set()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200 ),
        ]);
        $rating = new CSPRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals('The header is not set.', $rating->getComment());
    }

    /** @test */
    public function cspRating_rates_c_because_header_is_set_with_unsafe_inline()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                "Content-Security-Policy" => "default-src 'none'; script-src 'unsafe-inline'; object-src 'none';",
            ] ),
        ]);
        $rating = new CSPRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals('The header contains "unsafe-inline" or "unsafe-eval" directives.', $rating->getComment());
    }

    /** @test */
    public function cspRating_rates_c_because_header_is_set_with_unsafe_eval()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                "Content-Security-Policy" => "default-src 'none'; script-src 'unsafe-eval'; object-src 'none';",
            ] ),
        ]);
        $rating = new CSPRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals('The header contains "unsafe-inline" or "unsafe-eval" directives.', $rating->getComment());
    }

    /** @test */
    public function cspRating_rates_b_because_header_is_set_without_unsafes_but_without_default_src_none()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                "Content-Security-Policy" => "default-src 'self';",
            ] ),
        ]);
        $rating = new CSPRating("http://testdomain", $client);

        $this->assertEquals("B", $rating->getRating());
        $this->assertEquals('The header is free of any "unsafe-" directives.', $rating->getComment());
    }

    /** @test */
    public function cspRating_rates_a_because_header_is_set_without_unsafes_and_with_default_src_none()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                "Content-Security-Policy" => "default-src 'none';",
            ] ),
        ]);
        $rating = new CSPRating("http://testdomain", $client);

        $this->assertEquals("A", $rating->getRating());
        $this->assertEquals('The header is "unsafe-" free and includes "default-src \'none\'"', $rating->getComment());
    }

    // TODO: Rates with C because of wildcards in script-src, object-src or default-src
    // TODO: default-src *
    // TODO: default-src 'none'; script-src *;

    /** @test */
    public function cspRating_adds_comment_for_legacy_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                "X-Content-Security-Policy" => "default-src 'none';",
            ] ),
        ]);
        $rating = new CSPRating("http://testdomain", $client);

        $this->assertStringEndsWith("The legacy header \"X-Content-Security-Policy\" (that is only used for IE11 with CSP v.1) is set. The new and standardized header is Content-Security-Policy.", $rating->getComment());
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
