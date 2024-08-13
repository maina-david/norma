<?php

namespace Tests\Unit\Services\Html;

use App\Services\Html\ExtractBetween;
use Tests\TestCase;

class ExtractBetweenTest extends TestCase
{
    public function testBetweenIds(): void
    {
        $html = $this->getHtml();
        $extracted = app(ExtractBetween::class)->betweenIds($html, 'section1', 'section2');
        $this->assertStringContainsString('Section 1', $extracted);
        $this->assertStringNotContainsString('Section 2', $extracted);
        $this->assertStringNotContainsString('not inside common node', $extracted);
        $this->assertStringNotContainsString('</body>', $extracted);

        $extracted = app(ExtractBetween::class)->betweenIds($html, 'section2');
        $this->assertStringContainsString('Section 2', $extracted);
        $this->assertStringNotContainsString('Section 1', $extracted);
        $this->assertStringContainsString('not inside common node after section 2', $extracted);
        $this->assertStringNotContainsString('</body>', $extracted);

        $html = '<div id="section1">Section 1</div><div id="section2">Section 2</div>';
        $extracted = app(ExtractBetween::class)->betweenIds($html, 'section1', 'section2');
        $this->assertStringContainsString('Section 1', $extracted);
        $this->assertStringNotContainsString('Section 2', $extracted);
        $this->assertStringNotContainsString('</body>', $extracted);

        $extracted = app(ExtractBetween::class)->betweenIds($html, 'section2');
        $this->assertStringContainsString('Section 2', $extracted);
        $this->assertStringNotContainsString('Section 1', $extracted);
        $this->assertStringNotContainsString('</body>', $extracted);

        // test anchor tags not ID
        $html = $this->getHtmlWithAnchors();
        $extracted = app(ExtractBetween::class)->betweenIds($html, 'section3', 'section4');
        $this->assertStringContainsString('Section 3', $extracted);
        $this->assertStringNotContainsString('Section 4', $extracted);
    }

    public function testBetweenSelectors(): void
    {
        $html = $this->getHtml();
        $extracted = app(ExtractBetween::class)->betweenSelectors($html, '#section1', '#section2');
        $this->assertStringContainsString('Section 1', $extracted);
        $this->assertStringNotContainsString('Section 2', $extracted);
    }

    private function getHtml(): string
    {
        return '
    <div>
    <p>not inside common node</p>
    </div>

    <div>

        <p>Pellentesque sed lacus porttitor, molestie nisi eu, ornare velit. Cras mattis pulvinar tortor, quis viverra libero sollicitudin ac. Fusce sed mauris sit amet felis tincidunt dapibus ac ut libero. Nunc commodo turpis et leo feugiat, </span></p>
        <table>
            <tr></tr>
            <tr><td>vel ullamcorper ipsum elementum. Praesent aliquam dolor id mollis commodo.</td></tr>
        </table>
        <div>
            asdfasdfasdf
            <div><a id="section1"></a><span>Section 1</span></div>
            <div>
                <p></p>
            </div>
        </div>

        <div>
            <div>asdasdfg fg fg fg 55 fasdf</div>
            <div>asdasdfasdf</div>

            <div>asdf asdf <a id="section2"></a> Section 2</div>

            <div> Vestibulum ornare vulputate est ac sagittis. Suspendisse pretium in erat ac congue. Sed risus dolor, ultrices eu urna sit amet, condimentum ullamcorper sapien.</div>
            <div>Pellentesque sed lacus porttitor, molestie nisi eu, ornare velit. Cras mattis pulvinar tortor, quis viverra libero sollicitudin ac. Fusce sed mauris sit amet felis tinci</div>
            <div>fdfdf df df </div>
        </div>
    </div>
    <div>
    <p>not inside common node after section 2</p>
    </div>';
    }

    private function getHtmlWithAnchors(): string
    {
        return '
    <div>
    <p>not inside common node</p>
    </div>

    <div>

        <p>Pellentesque sed lacus porttitor, molestie nisi eu, ornare velit. Cras mattis pulvinar tortor, quis viverra libero sollicitudin ac. Fusce sed mauris sit amet felis tincidunt dapibus ac ut libero. Nunc commodo turpis et leo feugiat, </span></p>
        <table>
            <tr></tr>
            <tr><td>vel ullamcorper ipsum elementum. Praesent aliquam dolor id mollis commodo.</td></tr>
        </table>
                <div>
            asdfasdfasdf
            <div><a name="section3"></a><span>Section 3</span></div>
            <div>
                <p></p>
            </div>
        </div>

        <div>
            <div>asdasdfg fg fg fg 55 fasdf</div>
            <div>asdasdfasdf</div>

            <div>asdf asdf <a name="section4"></a> Section 4</div>

            <div> Vestibulum ornare vulputate est ac sagittis. Suspendisse pretium in erat ac congue. Sed risus dolor, ultrices eu urna sit amet, condimentum ullamcorper sapien.</div>
            <div>Pellentesque sed lacus porttitor, molestie nisi eu, ornare velit. Cras mattis pulvinar tortor, quis viverra libero sollicitudin ac. Fusce sed mauris sit amet felis tinci</div>
            <div>fdfdf df df </div>
        </div>
    </div>
    <div>
    <p>not inside common node after section 2</p>
    </div>';
    }
}
