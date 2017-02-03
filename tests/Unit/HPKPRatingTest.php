<?php

namespace Tests\Unit;

use App\Ratings\HPKPRating;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;


class HPKPRatingTest extends TestCase
{
    /** @test */
    public function hpkpRating_rates_c_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200 ),
        ]);
        $rating = new HPKPRating("http://testdomain", $client);

        $this->assertEquals("C", $rating->getRating());
        $this->assertEquals("The header is not set.", $rating->getComment());
    }

    /** @test */
    public function hpkpRating_rates_b_for_a_short_max_age()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                'Public-Key-Pins' => 'max-age=100000; pin-sha256="E9CZ9INDbd+2eRQozYqqbQ2yXLVKB9+xcprMF+44U1g="; pin-sha256="LPJNul+wow4m6DsqxbninhsWHlwfp0JecwQzYpOLmCQ=";'
            ]),
        ]);
        $rating = new HPKPRating("http://testdomain", $client);

        $this->assertEquals("B", $rating->getRating());
        $this->assertEquals('The keys are pinned for less than 15 days.', $rating->getComment());
    }

    /** @test */
    public function hpkpRating_rates_a_for_a_good_max_age()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                'Public-Key-Pins' => 'max-age=1500000; pin-sha256="E9CZ9INDbd+2eRQozYqqbQ2yXLVKB9+xcprMF+44U1g="; pin-sha256="LPJNul+wow4m6DsqxbninhsWHlwfp0JecwQzYpOLmCQ=";'
            ]),
        ]);
        $rating = new HPKPRating("http://testdomain", $client);

        $this->assertEquals("A", $rating->getRating());
        $this->assertEquals('The keys are pinned for more than 15 days.', $rating->getComment());
    }

    /** @test */
    public function hpkpRating_rates_x_plus_for_includeSubDomains()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                'Public-Key-Pins' => 'max-age=1000000; pin-sha256="E9CZ9INDbd+2eRQozYqqbQ2yXLVKB9+xcprMF+44U1g="; pin-sha256="LPJNul+wow4m6DsqxbninhsWHlwfp0JecwQzYpOLmCQ="; includeSubDomains'
            ]),
        ]);
        $rating = new HPKPRating("http://testdomain", $client);

        $this->assertStringEndsWith("+", $rating->getRating());
        $this->assertStringEndsWith('"includeSubDomains" is set.', $rating->getComment());
    }

    /** @test */
    public function hpkpRating_rates_x_plus_for_report_uri()
    {
        $client = $this->getMockedGuzzleClient([
            new Response( 200, [
                'Public-Key-Pins' => 'max-age=1000000; pin-sha256="E9CZ9INDbd+2eRQozYqqbQ2yXLVKB9+xcprMF+44U1g="; pin-sha256="LPJNul+wow4m6DsqxbninhsWHlwfp0JecwQzYpOLmCQ="; report-uri="http://example.com/pkp-report";'
            ]),
        ]);
        $rating = new HPKPRating("http://testdomain", $client);

        $this->assertStringEndsWith("+", $rating->getRating());
        $this->assertStringEndsWith('A report-uri is set.', $rating->getComment());
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
