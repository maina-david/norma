<?php

namespace App\Jobs\Compilation;

use App\Actions\Compilation\Autocompilation\HandleAutoCompilationExcelReportImport;
use App\Contracts\System\TrackableJobInterface;
use App\Jobs\Traits\Trackable;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoCompilationExcelImport implements ShouldQueue, TrackableJobInterface
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected Organisation $organisation, protected string $filePath, protected User $user)
    {
        $this->prepareStatus();
        $this->setProgressMax(100);
        $this->onQueue('compilation');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $progressCallback = function ($progress) {
            $this->setProgressNow($progress);
        };

        app(HandleAutoCompilationExcelReportImport::class)
            ->handle($this->organisation, $this->filePath, $this->user, $progressCallback);
    }
}
