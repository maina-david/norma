<?php

namespace Tests\Unit\Services\Arachno;

use App\Services\Arachno\Ocr\PdfOcr;
use App\Services\Arachno\OcrMyPdf;
use Mockery\MockInterface;
use Tests\TestCase;

class PdfOcrTest extends TestCase
{
    public function testOcr(): void
    {
        $contents = 'Fake pdf contents';
        $ocrOptions = [
            'provider' => 'ocr_my_pdf',
            'languages' => ['eng', 'spa'],
        ];
        $ocrOptionsNoProvider = [
            'languages' => ['eng', 'spa'],
        ];
        $expectedOcrString = '-l eng+spa --force-ocr';

        // ocr method of OcrMyPdf class will be if provider is set to ocr_my_pdf or not set at all
        $this->partialMock(OcrMyPdf::class, function (MockInterface $mock) use ($contents, $expectedOcrString) {
            $mock->shouldReceive('ocr')->with($contents, $expectedOcrString)->twice();
        });

        app(PdfOcr::class)->ocr($contents, $ocrOptions);
        $this->assertTrue(true);

        app(PdfOcr::class)->ocr($contents, $ocrOptionsNoProvider);
        $this->assertTrue(true);
    }
}
