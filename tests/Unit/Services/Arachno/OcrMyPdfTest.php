<?php

namespace Tests\Unit\Services\Arachno;

use App\Services\Arachno\OcrMyPdf;
use Process;
use Storage;
use Tests\TestCase;

class OcrMyPdfTest extends TestCase
{
    public function testOcr(): void
    {
        Process::fake();
        Storage::fake();

        $contents = 'Fake pdf contents';
        app(OcrMyPdf::class)->ocr($contents, '--language eng --force-ocr');

        Process::assertRan(fn ($process, $result) => str_contains($process->command, config('services.ocrmypdf.docker_run_command'))
            && str_contains($process->command, 'app:ocr-my-pdf')
            && str_contains($process->command, 'ocrmypdf'));
    }
}
