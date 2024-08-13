<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Enums\Arachno\Parse\StringTransformFunction;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Frontier\UrlFrontierLinkManager;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\LinkExtractor;
use Mockery\MockInterface;

class LinkExtractorTest extends AbstractDomParseTests
{
    public function testExtractLinksForQueries(): void
    {
        $this->partialMock(UrlFrontierLinkManager::class, function (MockInterface $mock) {
            $mock->shouldReceive('addFromPageCrawl');
            $mock->shouldReceive('addMetaToLink');
        });

        $this->prep();
        $queries = [
            new DomQuery(['type' => DomQueryType::CSS, 'transforms' => [['function' => StringTransformFunction::replace, 'args' => ['http:', 'https:']]], 'query' => '#toc a']),
            new DomQuery(['type' => DomQueryType::XPATH, 'transforms' => [['function' => StringTransformFunction::replace, 'args' => ['http:', 'https:']]], 'query' => '//a']),
        ];
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make(['url' => 'example.com']);
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, []);

        foreach ($queries as $q) {
            app(LinkExtractor::class)->extractLinksForQuery($q, $pageCrawl);
        }
        $this->assertTrue(true);
    }
}
