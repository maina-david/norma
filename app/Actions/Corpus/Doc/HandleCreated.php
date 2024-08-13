<?php

namespace App\Actions\Corpus\Doc;

use App\Models\Corpus\Doc;
use App\Models\Corpus\DocMeta;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleCreated
{
    use AsAction;

    public function handle(Doc $doc): void
    {
        $this->createDocMeta($doc);
    }

    private function createDocMeta(Doc $doc): void
    {
        DocMeta::create(['doc_id' => $doc->id]);
    }
}
