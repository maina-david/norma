<?php

namespace Tests\Unit\Services\Html;

use App\Services\Html\HtmlToText;
use Tests\TestCase;

class HtmlToTextTest extends TestCase
{
    public function testConvert(): void
    {
        $html = $this->getHtml();
        $converted = app(HtmlToText::class)->convert($html);
        $lines = explode("\n", $converted);
        $this->assertStringContainsString('Integer porta nibh ac massa ullamcorper, a consectetur sapien vehicula.', $converted);
        // span should be padded right
        $this->assertSame('(1) Integer porta nibh ac massa ullamcorper, a consectetur sapien vehicula.', $lines[0]);
        // multiple spaces converted to one
        $this->assertSame('(2) Sed ac ante ut dui interdum imperdiet eget eleifend lacus. ', $lines[1]);
        // table rows converted to lines with spaces between cells
        $this->assertSame('Row 1 Column 1 Row 1 Column 2', $lines[2]);
        $this->assertSame('Row 2 Column 1 Row 2 Column 2', $lines[3]);

        $this->assertSame('(3) Donec auctor purus viverra, placerat magna et, scelerisque ante.', $lines[4]);
        $this->assertSame('Should be a new line', $lines[5]);
        $this->assertSame('(4) Donec auctor purus viverra, placerat magna et, scelerisque ante.', $lines[6]);
        $this->assertSame('Should be a new line after br', $lines[7]);
    }

    private function getHtml(): string
    {
        return <<<HTML
<div>
    <div>
        <div>
            <p><span>(1)</span>Integer porta nibh
            ac massa ullamcorper,
            a consectetur sapien vehicula.<em></em><!-- Empty inline and this comment should be ignored--></p>
        </div>
    </div>
</div>
<div>
    <div>
        <div>
            <p><span>(2)</span>Sed ac ante ut dui interdum imperdiet eget eleifend lacus.                      </p>
        </div>
    </div>
</div>
<div>
    <table>
        <tbody>
            <tr><td>Row 1 Column 1</td><td>Row 1 Column 2</td></tr>
            <tr><td>Row 2 Column 1</td><td>Row 2 Column 2</td></tr>
        </tbody>
    </table>
</div>
<div>
    <div>
        <div>
            <p><span>(3)</span>Donec auctor purus viverra, placerat magna et, scelerisque ante.<div>Should be a new line</div></p>
        </div>
    </div>
</div>
<div>
    <div>
        <div>
            <p><span>(4)</span>Donec auctor purus viverra<img src="http://example.com/image.jpg"/>, placerat magna et, scelerisque ante.<br/>Should be a new line after br</p>
        </div>
    </div>
</div>


HTML;
    }
}
