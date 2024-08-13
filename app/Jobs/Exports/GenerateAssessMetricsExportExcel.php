<?php

namespace App\Jobs\Exports;

use App\Contracts\System\TrackableJobInterface;
use App\Exports\Assess\AssessMetricsExcelExport;
use App\Jobs\Traits\Trackable;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateAssessMetricsExportExcel implements ShouldQueue, TrackableJobInterface
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * @param string               $tempFileName
     * @param Organisation         $organisation
     * @param Libryo|null          $libryo       ,
     * @param User                 $user
     * @param array<string, mixed> $filters
     */
    public function __construct(
        protected string $tempFileName,
        protected Organisation $organisation,
        protected ?Libryo $libryo,
        protected User $user,
        protected array $filters = [],
    ) {
        $this->prepareStatus();
        $this->setProgressMax(100);
        $this->onQueue('exports');
    }

    /**
     * @param AssessMetricsExcelExport $exporter
     *
     * @return void
     */
    public function handle(AssessMetricsExcelExport $exporter): void
    {
        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $this->tempFileName;

        $progressCallback = function ($progress) {
            $this->setProgressNow($progress);
        };

        $excel = $exporter->forOrganisation(
            $this->organisation,
            $this->libryo,
            $this->user,
            $this->filters,
            $progressCallback
        );

        $writer = new Xlsx($excel);
        $localTmpPath = storage_path('app/tmp') . DIRECTORY_SEPARATOR . $this->tempFileName . '_tmp';
        if (!is_dir(storage_path('app/tmp'))) {
            // @codeCoverageIgnoreStart
            mkdir(storage_path('app/tmp'));
            // @codeCoverageIgnoreEnd
        }
        $writer->save($localTmpPath);
        /** @var string */
        $fileContents = file_get_contents($localTmpPath);
        Storage::put($path, $fileContents);
        $this->setProgressNow(100);
        unlink($localTmpPath);
    }
}
