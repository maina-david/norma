<?php

namespace App\Actions\Requirements\Summary;

use App\Models\Requirements\Summary;
use App\Services\HTMLPurifierService;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleSummarySaving
{
    use AsAction;

    public function __construct(protected HTMLPurifierService $htmlPurifierService)
    {
    }

    public function handle(Summary $summary): void
    {
        if ($summary->isDirty('summary_body') && !is_null($summary->summary_body)) {
            $summary->summary_body = $this->htmlPurifierService->cleanSummary($summary->summary_body);
        }
    }
}
