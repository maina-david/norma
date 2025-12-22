<?php

namespace Tests\Unit\Services\Corpus;

use App\Services\Corpus\PrepareContentHtml;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class PrepareContentHtmlTest extends TestCase
{
    private function getTestHtml(): string
    {
        return '<html><head><title>Some Title</title><link href="css/style.css" rel="stylesheet" type="text/css" media="screen" /></head><body><div data-highlight-id="1234"></div></body></html>';
    }

    public function testCleanHtmlContent(): void
    {
        $content = $this->getTestHtml();
        $cleanContent = app(PrepareContentHtml::class)->cleanHtmlContent($content);
        $this->assertFalse(Str::contains($cleanContent, '<head>'));
        $this->assertStringContainsString('norma-full-text', $cleanContent);
        // check data-highlight-id= is replaced with id= and prefixed with id
        $this->assertStringContainsString(' id="id_1234"', $cleanContent);
        $crawler = new DomCrawler($cleanContent);
        // check that the stylesheet link was appended to the body
        $this->assertTrue(count($crawler->filter('.norma-full-text link')) > 0);
    }
}
