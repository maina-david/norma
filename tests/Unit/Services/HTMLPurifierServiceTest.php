<?php

namespace Tests\Unit\Services;

use App\Services\HTMLPurifierService;
use Tests\TestCase;

class HTMLPurifierServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testCleanComment(): void
    {
        $test = '<p style="text-align: center;"><script></script></p>';
        $html = app(HTMLPurifierService::class)->cleanComment($test);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('text-align', $html);
    }

    /**
     * @return void
     */
    public function testCleanTaskDescription(): void
    {
        $test = '<p style="text-align: center; font-weight: bold;"><table></table></p>';
        $html = app(HTMLPurifierService::class)->cleanTaskDescription($test);
        $this->assertStringNotContainsString('<table>', $html);
        $this->assertStringNotContainsString('font-weight', $html);
        $this->assertStringContainsString('text-align', $html);
    }

    /**
     * @return void
     */
    public function testCleanSection(): void
    {
        $test = '<p style="text-align: center; font-weight: bold;"><table></table></p>';
        $html = app(HTMLPurifierService::class)->cleanSection($test);
        $this->assertStringNotContainsString('<table>', $html);
        $this->assertStringContainsString('font-weight', $html);
        $this->assertStringContainsString('text-align', $html);
    }

    /**
     * @return void
     */
    public function testCleanSectionForPrinting(): void
    {
        $test = '<p style="text-align: center; font-weight: bold;"><style></style></p>';
        $html = app(HTMLPurifierService::class)->cleanSectionForPrinting($test);
        $this->assertStringNotContainsString('<style>', $html);
    }

    /**
     * @return void
     */
    public function testBasicWysiwyg(): void
    {
        $test = '<p style="text-align: center; font-weight: bold;"><a href="example.com">a Link</a><table></table></p>';
        $html = app(HTMLPurifierService::class)->cleanBasicWysiwyg($test);
        $this->assertStringNotContainsString('<table>', $html);
        $this->assertStringNotContainsString('font-weight', $html);
        $this->assertStringContainsString('text-align', $html);
        $this->assertStringContainsString('example.com', $html);
    }
}
