<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\DomQuerySource;
use App\Enums\Arachno\Parse\DomQueryType;
use App\Enums\Arachno\Parse\StringTransformFunction;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Parse\DomQuery;
use App\Services\Arachno\Parse\DomQueryRunner;

class DomQueryRunnerTest extends AbstractDomParseTests
{
    public function testQueryMatches(): void
    {
        $this->prep();

        $query = new DomQuery(['type' => DomQueryType::NESTED, 'operator' => 'and', 'query' => [['type' => DomQueryType::CSS, 'query' => 'body'], ['type' => DomQueryType::CSS, 'query' => 'div']]]);
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make();
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, []);

        $result = app(DomQueryRunner::class)->queryMatches($query, $pageCrawl);
        $this->assertTrue($result);

        $query = new DomQuery(['type' => DomQueryType::NESTED, 'operator' => 'or', 'query' => [['type' => DomQueryType::CSS, 'query' => '#nonExistentID'], ['type' => DomQueryType::CSS, 'query' => 'div']]]);
        $result = app(DomQueryRunner::class)->queryMatches($query, $pageCrawl);
        $this->assertTrue($result);

        $query = new DomQuery(['type' => DomQueryType::NESTED, 'operator' => 'and', 'query' => [['type' => DomQueryType::CSS, 'query' => '#nonExistentID'], ['type' => DomQueryType::CSS, 'query' => 'div']]]);
        $result = app(DomQueryRunner::class)->queryMatches($query, $pageCrawl);
        $this->assertFalse($result);

        $query = new DomQuery(['type' => DomQueryType::REGEX, 'query' => '/not-in-url/', 'source' => DomQuerySource::URL]);
        $result = app(DomQueryRunner::class)->queryMatches($query, $pageCrawl);
        $this->assertFalse($result);

        $testText = 'Hello World';
        /** @var ContentCache */
        $contentCache = ContentCache::factory()->create(['response_body' => json_encode(['some' => ['test' => ['data' => $testText]]]), 'response_headers' => ['Content-Type' => ['application/pdf']]]);
        $pageCrawl->setContentCache($contentCache);
        $query = new DomQuery(['type' => DomQueryType::JSON, 'query' => 'some.test.data']);
        $result = app(DomQueryRunner::class)->queryMatches($query, $pageCrawl);
        $this->assertTrue($result);

        $query = new DomQuery(['type' => DomQueryType::FUNC, 'query' => fn ($pgCrwl) => true]);
        $result = app(DomQueryRunner::class)->queryMatches($query, $pageCrawl);
        $this->assertTrue($result);
    }

    public function testGetTextByQuery(): void
    {
        $this->prep();
        $url = 'http://example.com';
        $anchorText = 'This will take you to example.com';
        /** @var UrlFrontierLink */
        $pageUrl = UrlFrontierLink::factory()->make(['url' => $url, 'anchor_text' => $anchorText]);
        $pageCrawl = new PageCrawl($this->domCrawler, $pageUrl, []);

        $query = new DomQuery(['type' => DomQueryType::REGEX, 'query' => '/(example)/', 'source' => DomQuerySource::URL]);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        $this->assertSame('example', $result);

        $query = new DomQuery(['type' => DomQueryType::REGEX, 'query' => '/(will).*(example)/', 'source' => DomQuerySource::ANCHOR_TEXT]);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        $this->assertSame('willexample', $result);

        $query = new DomQuery(['type' => DomQueryType::CSS, 'query' => '#nonExistent']);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        $this->assertNull($result);

        $query = new DomQuery(['type' => DomQueryType::XPATH, 'query' => '//meta[@name="work.date"]//@content']);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        $this->assertSame('24th of July, 2021', $result);

        $query = new DomQuery(['type' => DomQueryType::XPATH, 'query' => '//meta[@name="work.date"]//@content', 'pre_glue_transforms' => [['function' => StringTransformFunction::replace, 'args' => [',', '']]]]);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        // comma removed by pre_glue_transform
        $this->assertSame('24th of July 2021', $result);

        $query = new DomQuery(['type' => DomQueryType::XPATH, 'query' => '//meta[@name="work.date"]//@content', 'transforms' => [['function' => StringTransformFunction::replace, 'args' => [',', '']]]]);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        // comma removed by transforms
        $this->assertSame('24th of July 2021', $result);

        $testText = 'Hello World';
        /** @var ContentCache */
        $contentCache = ContentCache::factory()->create(['response_body' => json_encode(['some' => ['test' => ['data' => $testText]]]), 'response_headers' => ['Content-Type' => ['application/pdf']]]);
        $pageCrawl->setContentCache($contentCache);
        $query = new DomQuery(['type' => DomQueryType::JSON, 'query' => 'some.test.data']);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        $this->assertSame($testText, $result);

        $query = new DomQuery(['type' => DomQueryType::FUNC, 'query' => fn ($pgCrwl) => $testText]);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        $this->assertSame($testText, $result);

        $query = new DomQuery(['type' => DomQueryType::TEXT, 'query' => 'deu']);
        $result = app(DomQueryRunner::class)->getTextByQuery($query, $pageCrawl);
        $this->assertSame('deu', $result);
    }

    public function testExtractTextWithRegexQuery(): void
    {
        $text = 'http://example.com';
        $query = new DomQuery(['type' => DomQueryType::REGEX, 'query' => '/nomatch/']);
        $result = app(DomQueryRunner::class)->extractTextWithRegexQuery($query, $text);
        $this->assertSame($text, $result);

        $query = new DomQuery(['type' => DomQueryType::REGEX, 'query' => '/(example)/']);
        $result = app(DomQueryRunner::class)->extractTextWithRegexQuery($query, $text);
        $this->assertSame('example', $result);

        $query = new DomQuery(['type' => DomQueryType::REGEX, 'query' => '/(ex)amp(le)/']);
        $result = app(DomQueryRunner::class)->extractTextWithRegexQuery($query, $text);
        $this->assertSame('exle', $result);

        $query = new DomQuery(['type' => DomQueryType::REGEX, 'query' => '/example/']);
        $result = app(DomQueryRunner::class)->extractTextWithRegexQuery($query, $text);
        $this->assertSame('example', $result);
    }
}
