<?php

namespace Tests\Unit;

use App\DOMXSSCheck;
use Tests\TestCase;

class DOMXSSCheckTest extends TestCase
{
    /** @test */
    public function domxssCheckFindsSinks()
    {
        $sampleBody = file_get_contents(base_path().'/tests/Unit/hradek.test.html');

        $this->assertTrue(DOMXSSCheck::hasSinks($sampleBody));
    }

    /** @test */
    public function domxssCheckFindsSources()
    {
        $sampleBody = file_get_contents(base_path().'/tests/Unit/hradek.test.html');

        $this->assertTrue(DOMXSSCheck::hasSources($sampleBody));
    }
}
