<?php

namespace App\Jobs\Corpus;

use App\Services\Corpus\ReferenceTocItemMatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AttemptMatchingTocItemsToReferences implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $workId)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ReferenceTocItemMatcher $matcher): void
    {
        $matcher->attempt($this->workId);
    }
}
