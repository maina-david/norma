<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Services\Arachno\Parse\TocItemFinder;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class TocItemFinderTest extends TestCase
{
    public function testFindItemsDefault(): void
    {
        $html = file_get_contents('./tests/Unit/Services/Arachno/Parse/test-data.html');
        $crawler = new DomCrawler($html);

        $tocItems = [];
        foreach (app(TocItemFinder::class)->findItems($crawler->filter('#toc')) as $n) {
            $tocItems[] = $n;
        }

        $this->assertCount(
            count($crawler->filter('#toc a')),
            $tocItems,
            'Failed asserting that the right amount of ToC items were found'
        );

        $this->assertTrue($tocItems[1]['level'] === 1);
        $this->assertTrue($tocItems[2]['level'] === 2);
        $this->assertTrue($tocItems[4]['level'] === 3);
    }

    public function testFindItemsOnMunicode(): void
    {
        $html = file_get_contents('./tests/Unit/Services/Arachno/Parse/test-data-toc.html');
        $crawler = new DomCrawler($html);

        $tocItems = [];
        foreach (app(TocItemFinder::class)->findItems($crawler->filter('#toc')) as $n) {
            $tocItems[] = $n;
        }

        $this->assertCount(
            count($crawler->filter('#toc a[href]')),
            $tocItems,
            'Failed asserting that the right amount of ToC items were found'
        );

        $tocItems = [];
        $criteria = fn ($nodeDTO) => strtolower($nodeDTO['node']->nodeName) === 'a'
            && stripos($nodeDTO['node']->getAttribute('class'), 'toc-item-heading') !== false;
        foreach (app(TocItemFinder::class)->findItems($crawler->filter('#toc'), $criteria) as $n) {
            $tocItems[] = $n;
        }

        $this->assertCount(
            count($crawler->filter('#toc a[href]')),
            $tocItems,
            'Failed asserting that the right amount of ToC items were found with custom criteria'
        );

        $this->assertTrue($tocItems[0]['level'] === 1);
        $this->assertTrue($tocItems[1]['level'] === 2);
        $this->assertTrue($tocItems[2]['level'] === 3);

        $this->assertStringContainsString('Title 1 - GENERAL PROVISIONS', $tocItems[0]['label']);
        $this->assertStringContainsString('Chapter 1.01 - CODE ADOPTION', $tocItems[1]['label']);
    }

    public function testWithCallbacks(): void
    {
        $html = file_get_contents('./tests/Unit/Services/Arachno/Parse/test-data.html');
        $crawler = new DomCrawler($html);

        // this is just the default function - but we're just testing passing callbacks
        $criteria = function ($nodeDTO) {
            $href = $nodeDTO['node']->getAttribute('href');

            return strtolower($nodeDTO['node']->nodeName) === 'a' && $href && $href !== '#';
        };

        // this is just the default function - but we're just testing passing callbacks
        $extractLabel = fn ($nodeDto) => $nodeDto['node']->textContent;
        // this is just the default function - but we're just testing passing callbacks
        $extractLink = fn ($nodeDto) => $nodeDto['node']->getAttribute('href');
        // just set depth to 1
        $determineDepth = fn ($nodeDto) => 1;
        $uniqueIdFunc = fn ($nodeDto) => '12345';

        $tocItems = [];
        foreach (app(TocItemFinder::class)->findItems($crawler->filter('#toc'), $criteria, $extractLabel, $extractLink, $uniqueIdFunc, $determineDepth) as $n) {
            $tocItems[] = $n;
        }
        $this->assertTrue($tocItems[0]['level'] === 1);
        $this->assertTrue($tocItems[1]['level'] === 1);
    }
}
