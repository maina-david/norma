<?php

namespace Tests\Unit\Services\Arachno\Parse;

use App\Enums\Arachno\Parse\PdfProperty;
use App\Services\Arachno\Parse\PdfExtractor;
use Tests\TestCase;

class PdfExtractorTest extends TestCase
{
    public function testGetProperty(): void
    {
        $pdf = file_get_contents('./tests/Unit/Services/Arachno/Parse/test.pdf');

        $this->assertSame('Libryo Test PDF', app(PdfExtractor::class)->getProperty(PdfProperty::TITLE, $pdf));
        $this->assertSame('Libryo test pdf subject', app(PdfExtractor::class)->getProperty(PdfProperty::SUBJECT, $pdf));
        $this->assertSame('libryo, test, pdf', app(PdfExtractor::class)->getProperty(PdfProperty::KEYWORDS, $pdf));
        $this->assertSame('Writer', app(PdfExtractor::class)->getProperty(PdfProperty::CREATOR, $pdf));
        $this->assertSame('LibreOffice 7.2', app(PdfExtractor::class)->getProperty(PdfProperty::PRODUCER, $pdf));
        $this->assertSame('2022-04-13', explode('T', app(PdfExtractor::class)->getProperty(PdfProperty::CREATION_DATE, $pdf))[0]);
        $this->assertStringContainsString('ipsum', app(PdfExtractor::class)->getText($pdf));
    }
}
