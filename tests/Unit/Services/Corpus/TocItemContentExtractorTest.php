<?php

namespace Tests\Unit\Services\Corpus;

use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Services\Corpus\TocItemContentExtractor;
use App\Stores\Corpus\ContentResourceStore;
use Mockery\MockInterface;
use Tests\TestCase;

class TocItemContentExtractorTest extends TestCase
{
    public function testExtract(): void
    {
        $this->mock(ContentResourceStore::class, function (MockInterface $mock) {
            $content = $this->getHtml();
            $mock->shouldReceive('get')->andReturn($content);
        });

        $contentResource = ContentResource::factory()->create(['path' => '/']);
        $doc = Doc::factory()->create();
        $tocItems = TocItem::factory(3)
            ->for($doc)
            ->sequence(fn ($sequence) => ['uri_fragment' => 'section' . ($sequence->index + 1)])
            ->sequence(fn ($sequence) => ['doc_position' => $sequence->index + 1])
            ->create(['content_resource_id' => $contentResource->id]);
        $items = app(TocItemContentExtractor::class)->extract($doc);
        $this->assertStringContainsString('Section 1', $items[0]->_content);
        $this->assertStringNotContainsString('Section 2', $items[0]->_content);
        $this->assertStringNotContainsString('Section 3', $items[0]->_content);

        $this->assertStringContainsString('Section 2', $items[1]->_content);
        $this->assertStringNotContainsString('Section 1', $items[1]->_content);
        $this->assertStringNotContainsString('Section 3', $items[1]->_content);

        $this->assertStringContainsString('Section 3', $items[2]->_content);
        $this->assertStringNotContainsString('Section 1', $items[2]->_content);
        $this->assertStringNotContainsString('Section 2', $items[2]->_content);

        $content = app(TocItemContentExtractor::class)->extractItem($tocItems[0]);
        $this->assertStringContainsString('Section 1', $content);

        // if no uri fragment, it should return all content
        $tocItems[0]->update(['uri_fragment' => null]);
        $items = app(TocItemContentExtractor::class)->extract($doc);
        $this->assertStringContainsString('Section 1', $items[0]->_content);
        $this->assertStringContainsString('Section 2', $items[0]->_content);
        $this->assertStringContainsString('Section 3', $items[0]->_content);
    }

    private function getHtml(): string
    {
        return '<html><head></head><body><div id="section1">Section 1</div><div id="section2">Section 2</div><div id="section3">Section 3</div></body>';
    }
}
