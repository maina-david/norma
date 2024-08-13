<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Contracts\Exports\SpreadsheetMapper;
use App\Contracts\System\TrackableJobInterface;
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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class LibryoAndOrganisationExport implements ShouldQueue, TrackableJobInterface
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Trackable;

    /** @var string */
    protected string $route;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 600;

    /**
     * @param string               $tempFileName
     * @param User                 $user
     * @param Libryo|null          $libryo
     * @param Organisation         $organisation
     * @param array<string, mixed> $filters
     * @param string|null          $spreadsheetMapper
     */
    public function __construct(
        protected string $tempFileName,
        protected User $user,
        protected ?Libryo $libryo,
        protected Organisation $organisation,
        protected array $filters = [],
        protected ?string $spreadsheetMapper = null
    ) {
        $this->prepareStatus();
        $this->setProgressMax(100);
        $this->onQueue('exports');
        $this->route = route('my.dashboard');
    }

    /**
     * @return \App\Contracts\Exports\LibryoOrganisationExport
     */
    abstract protected function getExporter(): LibryoOrganisationExport;

    /**
     * Get the spreadsheet mapper to be used.
     *
     * @return \App\Contracts\Exports\SpreadsheetMapper|null
     */
    protected function getSpreadsheetMapper(): ?SpreadsheetMapper
    {
        return $this->spreadsheetMapper ? app($this->spreadsheetMapper) : null;
    }

    /**
     * Get the writer to be used.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     *
     * @return \PhpOffice\PhpSpreadsheet\Writer\BaseWriter
     */
    protected function getWriter(Spreadsheet $spreadsheet): BaseWriter
    {
        return new Xlsx($spreadsheet);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     *
     * @return void
     */
    public function handle(): void
    {
        $exporter = $this->getExporter();

        if (method_exists($exporter, 'setMapper')) {
            $exporter->setMapper($this->spreadsheetMapper);
        }

        $mapper = $this->getSpreadsheetMapper();

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $this->tempFileName;

        $progressCallback = fn ($progress) => $this->setProgressNow($progress);

        if (!is_null($this->libryo)) {
            $excel = $exporter->forLibryo($this->libryo, $this->organisation, $this->user, $this->filters, $progressCallback);
        } else {
            $excel = $exporter->forOrganisation($this->organisation, $this->user, $this->filters, $progressCallback);
        }

        $writer = $mapper?->writer($excel) ?? $this->getWriter($excel);

        $localTmpPath = storage_path('app/tmp') . DIRECTORY_SEPARATOR . $this->tempFileName . '_tmp';

        if (!is_dir(storage_path('app/tmp'))) {
            // @codeCoverageIgnoreStart
            mkdir(storage_path('app/tmp'));
            // @codeCoverageIgnoreEnd
        }

        $writer->save($localTmpPath);

        /** @var string $fileContents */
        $fileContents = file_get_contents($localTmpPath);
        Storage::put($path, $fileContents);
        $this->setProgressNow(100);
        @unlink($localTmpPath);
    }
}
