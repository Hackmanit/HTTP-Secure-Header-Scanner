<?php

namespace Tests\Unit;

use App\HTTPResponse;
use App\Ratings\ReferrerPolicyRating;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class ReferrerPolicyRatingTest extends TestCase
{
    /** @test */
    public function referrerPolicy_rates_0_for_a_missing_header()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ReferrerPolicyRating($response);

        $this->assertEquals(0, $rating->score);
        $expected = [
            'placeholder' => 'HEADER_NOT_SET',
            'values'      => null,
        ];
        $this->assertEquals($expected, $rating->errorMessage);
    }

    /** @test */
    public function referrerPolicy_detects_wrong_encoding()
    {
        $client = $this->getMockedGuzzleClient([
            // Producing an encoding error
            new Response(200, ['Referrer-Policy' => zlib_encode('SGVsbG8gV29ybGQ=', ZLIB_ENCODING_RAW)]),
        ]);
        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ReferrerPolicyRating($response);

        $this->assertEquals(0, $rating->score);
        $this->assertTrue(collect($rating->errorMessage)->contains('HEADER_ENCODING_ERROR'));
        $this->assertTrue($rating->hasError);
    }

    /** @test */
    public function referrerPolicy_rates_100_for_privacy_protecting_directives()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Referrer-Policy' => 'no-referrer']),
            new Response(200, ['Referrer-Policy' => 'same-origin']),
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $response = new HTTPResponse('https://testdomain', $client);
            $rating = new ReferrerPolicyRating($response);
            $this->assertEquals(100, $rating->score);
            $this->assertFalse($rating->hasError);
        }
    }

    /** @test */
    public function referrerPolicy_rates_70_for_downgrade_protective_directives()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Referrer-Policy' => 'strict-origin']),
            new Response(200, ['Referrer-Policy' => 'strict-origin-when-cross-origin']),
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $response = new HTTPResponse('https://testdomain', $client);
            $rating = new ReferrerPolicyRating($response);
            $this->assertEquals(70, $rating->score);
            $this->assertFalse($rating->hasError);
        }
    }

    /** @test */
    public function referrerPolicy_rates_40_for_not_downgrade_protective_directives()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Referrer-Policy' => 'origin']),
            new Response(200, ['Referrer-Policy' => 'origin-when-cross-origin']),
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $response = new HTTPResponse('https://testdomain', $client);
            $rating = new ReferrerPolicyRating($response);
            $this->assertEquals(40, $rating->score);
            $this->assertFalse($rating->hasError);
        }
    }

    /** @test */
    public function referrerPolicy_rates_10_for_an_empty_directive()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Referrer-Policy' => '']),
        ]);

        $response = new HTTPResponse('https://testdomain', $client);
        $rating = new ReferrerPolicyRating($response);
        $this->assertEquals(10, $rating->score);
        $this->assertFalse($rating->hasError);
    }

    /** @test */
    public function referrerPolicy_rates_0_for_url_leaking_directives()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Referrer-Policy' => 'no-referrer-when-downgrade']),
            new Response(200, ['Referrer-Policy' => 'unsafe-url']),
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $response = new HTTPResponse('https://testdomain', $client);
            $rating = new ReferrerPolicyRating($response);
            $this->assertEquals(0, $rating->score);
            $this->assertFalse($rating->hasError);
        }
    }

    /** @test */
    public function referrerPolicy_rates_0_with_error_for_all_not_defined_directives()
    {
        $client = $this->getMockedGuzzleClient([
            new Response(200, ['Referrer-Policy' => '#no-referrer']),
            new Response(200, ['Referrer-Policy' => 'strange-config']),
        ]);

        for ($i = 1; $i <= 2; $i++) {
            $response = new HTTPResponse('https://testdomain', $client);
            $rating = new ReferrerPolicyRating($response);
            $this->assertEquals(0, $rating->score);
            $this->assertTrue($rating->hasError);
            $this->assertTrue(collect($rating->errorMessage)->contains('WRONG_DIRECTIVE_SET'));
        }
    }
}
