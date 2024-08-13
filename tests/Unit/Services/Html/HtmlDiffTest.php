<?php

namespace Tests\Unit\Services\Html;

use App\Services\Html\HtmlDiff;
use Tests\TestCase;

class HtmlDiffTest extends TestCase
{
    public function testCompare(): void
    {
        $s1 = <<<HTML
        <div>Line 1</div>
        <div>Line 2</div>
        <div>Line 3</div>
HTML;
        $s2 = <<<HTML
        <div>Line 1</div>
        <div>Line</div>
        <div>Line 3</div>
        <div>a new line</div>
HTML;

        $compared = app(HtmlDiff::class)->compare($s1, $s2);
        $lines = explode("\n", $compared);
        $this->assertSame('Line 1', $lines[0]);
        $this->assertSame('Line<del> 2</del>', $lines[1]);
        $this->assertSame('Line 3', $lines[2]);
        $this->assertSame('<ins>a new line', $lines[3]);
        $this->assertSame('</ins>', $lines[4]);
    }
}
