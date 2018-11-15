<?php

namespace Tests\Unit;

use App\CSPParser;
use Tests\TestCase;

class CSPParserTest extends TestCase
{
    /** @test */
    public function cspParser_accepts_a_complete_header_definition()
    {
        $header = "Content-Security-Policy: default-src 'none'; script-src 'self'";

        $csp = new CSPParser($header);

        $this->assertEquals($csp->headerName, 'Content-Security-Policy');
        $this->assertEquals($csp->originalDirectivesString, "default-src 'none'; script-src 'self'");
        $this->assertFalse($csp->hasLegacyHeader);
    }

    /** @test */
    public function cspParser_accepts_a_complete_legacy_header_definition()
    {
        $header = "X-Content-Security-Policy: default-src 'none'; script-src 'self'";

        $csp = new CSPParser($header);

        $this->assertEquals($csp->headerName, 'X-Content-Security-Policy');
        $this->assertEquals($csp->originalDirectivesString, "default-src 'none'; script-src 'self'");
        $this->assertTrue($csp->hasLegacyHeader);
    }

    /** @test */
    public function cspParser_gets_the_correct_amount_of_directives()
    {
        $header = "Content-Security-Policy: default-src 'none'; script-src 'self'   ; font-src 'none'";

        $csp = new CSPParser($header);

        $this->assertEquals(3, count($csp->directives));
    }

    /** @test */
    public function cspParser_gets_the_correct_amount_of_directives_and_ignores_valid_whitespace()
    {
        $header = "Content-Security-Policy:        default-src  'none';      script-src   'self';       font-src   'none'  ";

        $csp = new CSPParser($header);

        $this->assertEquals(3, count($csp->directives));
    }

    /** @test */
    public function cspParser_returns_a_correct_directives_collection()
    {
        $header = "Content-Security-Policy: default-src 'none'; script-src 'self'; font-src 'self' *.gstatic.com data:";

        $csp = new CSPParser($header);

        $this->assertEquals(collect(["'none'"]), $csp->directives->get('default-src'));
        $this->assertEquals(collect(["'self'"]), $csp->directives->get('script-src'));
        $this->assertEquals(collect(["'self'", '*.gstatic.com', 'data:']), $csp->directives->get('font-src'));
    }

    /** @test */
    public function cspParser_returns_a_correct_directives_collection_and_ignores_whitespace()
    {
        $header = "Content-Security-Policy: default-src     'none'  ; script-src     'self'   ; font-src 'self'   *.gstatic.com data:   ";

        $csp = new CSPParser($header);

        $this->assertEquals(collect(["'none'"]), $csp->directives->get('default-src'));
        $this->assertEquals(collect(["'self'"]), $csp->directives->get('script-src'));
        $this->assertEquals(collect(["'self'", '*.gstatic.com', 'data:']), $csp->directives->get('font-src'));
    }

    /** @test */
    public function cspParser_checks_for_invalid_directives()
    {
        $header = "Content-Security-Policy: default-src 'none'; script-src 'self'; font-src 'self' *.gstatic.com data:";
        $csp = new CSPParser($header);
        $this->assertTrue($csp->isValid());

        $header = "Content-Security-Policy: #default-src 'none'; script-src 'self'; font-src 'self' *.gstatic.com data:";
        $csp = new CSPParser($header);
        $this->assertFalse($csp->isValid());
    }

    /** @test */
    public function cspParser_checks_for_invalid_values()
    {
        $headers = [
            // Non-ASCII-character
            "Content-Security-Policy: default-src 'none'; script-src Электронные словари",
            // Comma
            "Content-Security-Policy: default-src 'none'; script-src hallo,welt.de",
        ];

        foreach ($headers as $header) {
            $csp = new CSPParser($header);
            $this->assertFalse($csp->isValid());
        }
    }

    /** @test */
    public function cspParser_allows_nonces()
    {
        $header = "Content-Security-Policy: script-src 'self' 'nonce-b8c1HAq0yLmcbxTyRUb+pZlRX8U='";
        $csp = new CSPParser($header);
        $this->assertTrue($csp->isValid());
    }

    /** @test */
    public function cspParser_allows_hashes()
    {
        $header = "Content-Security-Policy: script-src 'sha256-gPMJwWBMWDx0Cm7ZygJKZIU2vZpiYvzUQjl5Rh37hKs='";
        $csp = new CSPParser($header);
        $this->assertTrue($csp->isValid());
    }
}
