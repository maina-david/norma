<?php

namespace App\Observers\Corpus;

use App\Actions\Corpus\Doc\HandleCreated;
use App\Models\Corpus\Doc;
use App\Observers\Observer;

class DocObserver extends Observer
{
    public function created(Doc $doc): void
    {
        app(HandleCreated::class)->handle($doc);
    }
}
