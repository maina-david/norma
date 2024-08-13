<?php

namespace App\Jobs\Arachno;

use App\Models\Arachno\ContentCache;
use App\Models\Corpus\Doc;
use App\Services\Arachno\Ocr\PdfOcr;
use App\Services\Arachno\Parse\PdfCapture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OcrPdf implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * creates a new job.
     *
     * @param int                  $docId
     * @param int                  $contentCacheId
     * @param array<string, mixed> $options
     */
    public function __construct(protected int $docId, protected int $contentCacheId, protected array $options)
    {
        $this->onQueue('ocr');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PdfOcr $ocrService, PdfCapture $pdfCapture): void
    {
        $doc = Doc::findOrFail($this->docId);
        $cache = ContentCache::findOrFail($this->contentCacheId);

        $content = $ocrService->ocr($cache->response_body ?? '', $this->options);
        $pdfCapture->capture(
            $doc,
            $content
        );
    }
}
