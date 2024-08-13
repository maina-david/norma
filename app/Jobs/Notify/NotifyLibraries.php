<?php

namespace App\Jobs\Notify;

use App\Services\Notify\LegalUpdatesProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyLibraries implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @param LegalUpdatesProcessor $processor
     *
     * @return void
     */
    public function handle(LegalUpdatesProcessor $processor): void
    {
        $processor->notifyLibraries();
    }
}
