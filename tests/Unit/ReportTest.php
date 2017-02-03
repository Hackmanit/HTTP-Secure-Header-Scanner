<?php

namespace Tests\Unit;

use App\Report;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReportTest extends TestCase
{
    /** @test */
    public function report_returns_insecure_as_default()
    {
        $mock = Mockery::mock(Report::class, ["http://testdomain"])->makePartial();

        $mock->shouldReceive("getContentSecurityPolicyRating")->andReturn("C");
        $mock->shouldReceive("getContentTypeRating")->andReturn("C");
        $mock->shouldReceive("getHttpPublicKeyPinningRating")->andReturn("C");
        $mock->shouldReceive("getHttpStrictTransportSecurityRating")->andReturn("C");
        $mock->shouldReceive("getXContentTypeOptionsRating")->andReturn("C");
        $mock->shouldReceive("getXFrameOptionsRating")->andReturn("C");
        $mock->shouldReceive("getXXSSProtectionRating")->andReturn("C");

        $this->assertEquals("C", $mock->rate()->siteRating);
    }

    // TODO: Further tests
}
