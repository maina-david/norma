<?php

namespace App\Actions\Notify\LegalUpdate;

use App\Models\Notify\LegalUpdate;
use App\Services\HTMLPurifierService;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleSaving
{
    use AsAction;

    public function __construct(protected HTMLPurifierService $htmlPurifierService)
    {
    }

    public function handle(LegalUpdate $legalUpdate): LegalUpdate
    {
        if ($legalUpdate->isDirty('highlights') && $legalUpdate->highlights) {
            $legalUpdate->highlights = $this->htmlPurifierService->cleanBasicWysiwyg($legalUpdate->highlights);
        }
        if ($legalUpdate->isDirty('update_report') && $legalUpdate->update_report) {
            $legalUpdate->update_report = $this->htmlPurifierService->cleanBasicWysiwyg($legalUpdate->update_report);
        }
        if ($legalUpdate->isDirty('changes_to_register') && $legalUpdate->changes_to_register) {
            $legalUpdate->changes_to_register = $this->htmlPurifierService->cleanBasicWysiwyg($legalUpdate->changes_to_register);
        }

        return $legalUpdate;
    }
}
