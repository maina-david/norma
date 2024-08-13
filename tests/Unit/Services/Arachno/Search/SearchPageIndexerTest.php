<?php

namespace Tests\Unit\Services\Arachno\Search;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\SearchPage;
use App\Models\Arachno\SearchPageMeta;
use App\Services\Arachno\Parse\PdfExtractor;
use App\Services\Arachno\Search\SearchPageIndexer;
use App\Services\Language\LanguageDetector;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchPageIndexerTest extends TestCase
{
    public function testSavePage(): void
    {
        $title = 'This is a titte';
        $body = '<html><head><title>' . $title . '</title></head><body></body></html>';
        $headers = ['Content-Type' => ['text/html']];
        /** @var ContentCache */
        $contentCache = ContentCache::factory()->create(['response_body' => $body, 'response_headers' => $headers]);
        /** @var SearchPage */
        $searchPage = SearchPage::factory()->create();
        /** @var SearchPageMeta */
        $searchPageMeta = SearchPageMeta::factory()->create(['search_page_id' => $searchPage->id]);

        $this->partialMock(LanguageDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('detect')->andReturn('deu');
        });

        app(SearchPageIndexer::class)->indexSearchPage($searchPage->id, $contentCache->id);

        $searchPage->refresh();
        $this->assertSame('deu', $searchPage->searchPageMeta?->language_code);
        $this->assertSame($title, $searchPage->searchPageMeta?->metadata['title'] ?? '');

        $searchPage->searchPageMeta->update(['metadata' => null]);

        $pdfText = 'This is text in pdf';
        $pdfTitle = 'This is title in pdf';
        $contentCache->update(['response_headers' => ['Content-Type' => ['application/pdf']]]);
        $this->partialMock(PdfExtractor::class, function (MockInterface $mock) use ($pdfText, $pdfTitle) {
            $mock->shouldReceive('getText')->andReturn($pdfText);
            $mock->shouldReceive('getProperty')->andReturn($pdfTitle);
        });
        app(SearchPageIndexer::class)->indexSearchPage($searchPage->id, $contentCache->id);
        $this->assertSame($pdfTitle, $searchPage->searchPageMeta?->refresh()->metadata['title'] ?? '');
    }
}
