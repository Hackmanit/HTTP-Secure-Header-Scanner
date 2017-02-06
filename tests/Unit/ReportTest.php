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

        $mock->shouldReceive("getContentSecurityPolicyRating")->andReturn("");
        $mock->shouldReceive("getContentTypeRating")->andReturn("");
        $mock->shouldReceive("getHttpPublicKeyPinningRating")->andReturn("");
        $mock->shouldReceive("getHttpStrictTransportSecurityRating")->andReturn("");
        $mock->shouldReceive("getXContentTypeOptionsRating")->andReturn("");
        $mock->shouldReceive("getXFrameOptionsRating")->andReturn("");
        $mock->shouldReceive("getXXSSProtectionRating")->andReturn("");

        $this->assertEquals("C", $mock->rate()->siteRating);
    }

    /** @test */
    public function report_returns_insecure_when_all_parts_are_insecure()
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

    /** @test */
    public function report_returns_secure_when_all_parts_are_secure()
    {
        $mock = Mockery::mock(Report::class, ["http://testdomain"])->makePartial();

        $mock->shouldReceive("getContentSecurityPolicyRating")->andReturn("A");
        $mock->shouldReceive("getContentTypeRating")->andReturn("A");
        $mock->shouldReceive("getHttpPublicKeyPinningRating")->andReturn("A");
        $mock->shouldReceive("getHttpStrictTransportSecurityRating")->andReturn("A");
        $mock->shouldReceive("getXContentTypeOptionsRating")->andReturn("A");
        $mock->shouldReceive("getXFrameOptionsRating")->andReturn("A");
        $mock->shouldReceive("getXXSSProtectionRating")->andReturn("A");

        $this->assertEquals("A++", $mock->rate()->siteRating);
    }


}
