<?php

namespace Tests\Unit\Services\Arachno\Support;

use App\Enums\Arachno\Parse\DomQueryType;
use App\Enums\Arachno\Parse\StringTransformFunction;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Support\ExtractTableLinks;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class ExtractTableLinksTest extends TestCase
{
    public function testEtract(): void
    {
        $dom = new DomCrawler('<html><body><table><tbody><tr><td><a href="/link-to-doc1.html">Doc 1 title</a></td><td>01/01/2022</td></tr><tr><td><a href="/link-to-doc2.html">Doc 2 title</a></td><td>02/01/2022</td></tr></tbody></table></body></html>');
        $pageCrawl = new PageCrawl($dom, new UrlFrontierLink(['url' => 'https://example.com']), []);

        $rowQuery = new DomQuery(['type' => DomQueryType::CSS, 'query' => 'table tr']);
        $linkQuery = new DomQuery(['type' => DomQueryType::CSS, 'query' => 'td:first-of-type a']);
        $metaQueries = [
            'title' => new DomQuery(['type' => DomQueryType::CSS, 'query' => 'td:first-of-type a']),
            'work_date' => new DomQuery(['type' => DomQueryType::CSS, 'query' => 'td:last-of-type', 'transforms' => [['function' => StringTransformFunction::prepend, 'args' => ['d/m/Y|']]]]),
        ];
        $links = app(ExtractTableLinks::class)->extract(
            $pageCrawl,
            $rowQuery,
            $linkQuery,
            $metaQueries
        );

        $this->assertSame('Doc 1 title', $links[0]->_metaDto->title);
        $this->assertSame('Doc 2 title', $links[1]->_metaDto->title);
        $this->assertSame('https://example.com/link-to-doc1.html', $links[0]->url);
        $this->assertSame('2022-01-01', $links[0]->_metaDto->work_date->format('Y-m-d'));

        $linkQuery = new DomQuery(['type' => DomQueryType::XPATH, 'query' => '//td//a/@href']);
        $links = app(ExtractTableLinks::class)->extract(
            $pageCrawl,
            $rowQuery,
            $linkQuery,
            $metaQueries
        );
        $this->assertSame('https://example.com/link-to-doc1.html', $links[0]->url);
    }
}
