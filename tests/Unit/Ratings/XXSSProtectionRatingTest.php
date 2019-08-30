<?php

namespace Tests\Unit;

use App\HTTPResponse;
use App\Ratings\XXSSProtectionRating;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class XXSSProtectionRatingTest extends TestCase
{
    /** @test */
    public function xXSSProtection_rates_0_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(0, $rating->score);
        $expected = [
            'translationStringId' => 'HEADER_NOT_SET',
            'placeholders' => null,
        ];
        $this->assertEquals($expected, $rating->errorMessage);
    }

    /** @test */
    public function xXSSProtection_rates_a_set_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['X-Xss-Protection' => '0']),
            new Response(200, ['X-Xss-Protection' => '1']),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(50, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('XXSS_CORRECT'));

        $response = new HTTPResponse($this->request, $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(50, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('XXSS_CORRECT'));
    }

    /** @test */
    public function xXSSProtection_rates_mode_block()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['X-Xss-Protection' => '1; mode=block']),
        ]);

        $response = new HTTPResponse($this->request, $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(100, $rating->score);
        $this->assertTrue(collect($rating->testDetails)->flatten()->contains('XXSS_BLOCK'));
    }

    /** @test */
    public function XXSSProtectionRating_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ['X-XSS-Protection' => zlib_encode('SGVsbG8gV29ybGQ=', ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse($this->request, $client);
        $rating = new XXSSProtectionRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->errorMessage)->contains('HEADER_ENCODING_ERROR'));
        $this->assertTrue($rating->hasError);
    }
}
