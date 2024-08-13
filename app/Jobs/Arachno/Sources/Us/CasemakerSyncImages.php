<?php

namespace App\Jobs\Arachno\Sources\Us;

use App\Services\Arachno\Crawlers\Sources\Us\CaseMakerImagesSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Can ignore as it's just calls the service.
 *
 * @codeCoverageIgnore
 */
class CasemakerSyncImages implements ShouldQueue
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
    public function __construct(protected string $dir)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CaseMakerImagesSync $syncImages)
    {
        $syncImages->sync($this->dir);
    }
}
