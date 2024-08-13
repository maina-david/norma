<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use Lorisleiva\Actions\Concerns\AsAction;

class RequestReferenceContentUpdate
{
    use AsAction;

    /**
     * @param Reference $reference
     * @param User|null $user
     *
     * @return void
     */
    public function handle(Reference $reference, ?User $user = null): void
    {
        $reference->load('htmlContent');
        ReferenceContentDraft::firstOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'title' => $reference->refPlainText?->plain_text,
            'html_content' => $reference->htmlContent?->cached_content,
            'requested_by_id' => $user?->id,
        ]);
    }
}
