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

        $mock->shouldReceive("getRating")->with("content-security-policy")->andReturn("");
        $mock->shouldReceive("getRating")->with("content-type")->andReturn("");
        $mock->shouldReceive("getRating")->with("public-key-pins")->andReturn("");
        $mock->shouldReceive("getRating")->with("strict-transport-security")->andReturn("");
        $mock->shouldReceive("getRating")->with("x-content-type-options")->andReturn("");
        $mock->shouldReceive("getRating")->with("x-frame-options")->andReturn("");
        $mock->shouldReceive("getRating")->with("x-xss-protection")->andReturn("");

        $this->assertEquals("C", $mock->rate()->siteRating);
    }

    /** @test */
    public function report_returns_insecure_when_all_parts_are_insecure()
    {
        $mock = Mockery::mock(Report::class, ["http://testdomain"])->makePartial();

        $mock->shouldReceive("getRating")->with("content-security-policy")->andReturn("C");
        $mock->shouldReceive("getRating")->with("content-type")->andReturn("C");
        $mock->shouldReceive("getRating")->with("public-key-pins")->andReturn("C");
        $mock->shouldReceive("getRating")->with("strict-transport-security")->andReturn("C");
        $mock->shouldReceive("getRating")->with("x-content-type-options")->andReturn("C");
        $mock->shouldReceive("getRating")->with("x-frame-options")->andReturn("C");
        $mock->shouldReceive("getRating")->with("x-xss-protection")->andReturn("C");

        $this->assertEquals("C", $mock->rate()->siteRating);
    }

    /** @test */
    public function report_returns_secure_when_all_parts_are_secure()
    {
        $mock = Mockery::mock(Report::class, ["http://testdomain"])->makePartial();

        $mock->shouldReceive("getRating")->with("content-security-policy")->andReturn("A");
        $mock->shouldReceive("getRating")->with("content-type")->andReturn("A");
        $mock->shouldReceive("getRating")->with("public-key-pins")->andReturn("A");
        $mock->shouldReceive("getRating")->with("strict-transport-security")->andReturn("A");
        $mock->shouldReceive("getRating")->with("x-content-type-options")->andReturn("A");
        $mock->shouldReceive("getRating")->with("x-frame-options")->andReturn("A");
        $mock->shouldReceive("getRating")->with("x-xss-protection")->andReturn("A");

        $this->assertEquals("A++", $mock->rate()->siteRating);
    }


}
