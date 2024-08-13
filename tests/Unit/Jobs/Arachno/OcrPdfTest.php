<?php

namespace Tests\Unit\Jobs\Arachno;

use App\Jobs\Arachno\OcrPdf;
use App\Models\Arachno\ContentCache;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Frontier\PageCrawl;
use App\Services\Arachno\Ocr\PdfOcr;
use App\Services\Arachno\Parse\PdfCapture;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Tests\TestCase;

class OcrPdfTest extends TestCase
{
    public function testHandle(): void
    {
        $pdfCaptureOptions = ['languages' => ['eng']];
        $doc = Doc::factory()->create(['source_unique_id' => '123']);
        $contentCache = ContentCache::factory()->create(['response_body' => 'Test Pdf Body', 'response_headers' => ['Content-Type' => ['application/pdf']]]);
        $pageCrawl = Mockery::mock(PageCrawl::class)->makePartial();
        $pageCrawl->shouldReceive('setContentCache')->andReturnSelf();
        $pageCrawl->__construct(new DomCrawler(), new UrlFrontierLink(), [], $contentCache);

        $pdfOcr = $this->mock(PdfOcr::class, function (MockInterface $mock) {
            $mock->shouldReceive('ocr')->once();
        });
        $pdfCapture = $this->partialMock(PdfCapture::class, function (MockInterface $mock) {
            $mock->shouldReceive('capture')->once();
        });

        $job = new OcrPdf($doc->id, $contentCache->id, $pdfCaptureOptions);
        $job->handle($pdfOcr, $pdfCapture);
    }
}
