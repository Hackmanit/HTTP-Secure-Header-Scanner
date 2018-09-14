<?php

namespace Tests\Unit;

use App\HTTPResponse;
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
            new Response(200),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HPKPRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertEquals($rating->errorMessage, 'HEADER_NOT_SET');
    }

    /** @test */
    public function hpkpRating_rates_includeSubDomains()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Public-Key-Pins' => 'max-age=1000000; pin-sha256="E9CZ9INDbd+2eRQozYqqbQ2yXLVKB9+xcprMF+44U1g="; pin-sha256="LPJNul+wow4m6DsqxbninhsWHlwfp0JecwQzYpOLmCQ="; includeSubDomains',
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HPKPRating($response);

        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('INCLUDE_SUBDOMAINS'));
    }

    /** @test */
    public function hpkpRating_rates_report_uri()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, [
                'Public-Key-Pins' => 'max-age=1000000; pin-sha256="E9CZ9INDbd+2eRQozYqqbQ2yXLVKB9+xcprMF+44U1g="; pin-sha256="LPJNul+wow4m6DsqxbninhsWHlwfp0JecwQzYpOLmCQ="; report-uri="http://example.com/pkp-report";',
            ]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HPKPRating($response);

        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('HPKP_REPORT_URI'));
    }

    /** @test */
    public function HPKPRating_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ['Public-Key-Pins' => zlib_encode('SGVsbG8gV29ybGQ=', ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new HPKPRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('HEADER_ENCODING_ERROR'));
    }

    /**
     * This method sets and activates the GuzzleHttp Mocking functionality.
     *
     * @param array $responses
     *
     * @return Client
     */
    protected function getMockedGuzzleClient(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        return new Client(['handler' => $handler]);
    }
}
