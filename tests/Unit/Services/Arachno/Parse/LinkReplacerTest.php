<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Models\Arachno\Link;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\LinkToUri;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\LinkReplacer;
use DOMElement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\DomCrawler\Crawler;

class LinkReplacerTest extends AbstractDomParseTests
{
    use DatabaseTransactions;

    public function testLinkCapture(): void
    {
        $this->prep();
        $base = 'https://example.com';
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make(['url' => $base]);

        $link = 'test.html';
        $link2 = 'test.html#1234';
        $link3 = 'https://example-outside.com/test.html#1234';
        $html = '<html><body><div><a href="' . $link . '" alt="Test title"/></div><div><a href="' . $link2 . '" alt="Test title 2"/></div><div><a href="' . $link3 . '" alt="Test title 3"/></div></body></html>';
        $domCrawler = new Crawler($html);

        $pageCrawl = new PageCrawl($domCrawler, $pageUrl, []);
        $newCrawler = app(LinkReplacer::class)->capture($pageCrawl);

        /** @var DOMElement */
        $a = $newCrawler->filter('body a')->first()->getNode(0);
        $href = Link::buildUid($base . '/' . $link);
        $this->assertEquals(LinkToUri::LINK_PREFIX . sha1($href) . '?l=' . urlencode($href), $a->getAttribute('href'));
        $this->assertEquals('_blank', $a->getAttribute('target'));

        /** @var DOMElement */
        $a = $newCrawler->filter('body a')->getNode(1);
        $originalHref = $base . '/' . $link2;
        $href = Link::buildUid($originalHref);
        $this->assertEquals(LinkToUri::LINK_PREFIX . sha1($href) . '?l=' . urlencode($originalHref), $a->getAttribute('href'));
        $this->assertEquals('_blank', $a->getAttribute('target'));

        /** @var DOMElement */
        $a = $newCrawler->filter('body a')->getNode(2);
        $href = Link::buildUid($base . '/' . $link3);
        $this->assertNotEquals(LinkToUri::LINK_PREFIX . sha1($href) . '?l=' . urlencode($href), $a->getAttribute('href'));
        $this->assertEquals($link3, $a->getAttribute('href'));
        $this->assertEquals('_blank', $a->getAttribute('target'));
    }
}
