<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * Generate filename from the ID.
     *
     * @param string $filename
     *
     * @return string
     */
    protected function generateFilename(string $filename): string
    {
        $parts = explode('---', $filename);
        $title = $parts[0] === 'l' ? (Libryo::find($parts[1])?->title ?? '') : (Organisation::find($parts[1])?->title ?? '');
        /** @var string $title */
        $title = str_replace('/', '_', $title);
        /** @var string $title */
        $title = str_replace('\\', '_', $title);

        return $title;
    }

    /**
     * @param string $filename
     * @param string $name
     *
     * @return BinaryFileResponse
     */
    protected function download(string $filename, string $name): BinaryFileResponse
    {
        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;

        if (!App::environment('testing')) {
            // @codeCoverageIgnoreStart
            usleep(100000);
            // @codeCoverageIgnoreEnd
        }

        $file = Storage::get($path);
        $localTmpPath = storage_path('app/tmp') . DIRECTORY_SEPARATOR . $filename . '_tmp';
        file_put_contents($localTmpPath, $file);

        Storage::delete($path);

        return response()->download($localTmpPath, $name)
            ->deleteFileAfterSend();
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadRequirementsExcel(string $filename): BinaryFileResponse
    {
        /** @var string */
        $requirementsTxt = __('requirements.requirements');
        $requirementsTxt = sprintf(
            '%s %s %s.xlsx',
            $this->generateFilename($filename),
            $requirementsTxt,
            now()->toDateString(),
        );

        return $this->download($filename, $requirementsTxt);
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadRequirementsPDF(string $filename): BinaryFileResponse
    {
        /** @var string */
        $requirementsTxt = __('requirements.requirements');
        $requirementsTxt = sprintf(
            '%s %s %s.pdf',
            $this->generateFilename($filename),
            $requirementsTxt,
            now()->toDateString(),
        );

        return $this->download($filename, $requirementsTxt);
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadLegalUpdatesExcel(string $filename): BinaryFileResponse
    {
        /** @var string */
        $updatesTxt = __('notify.legal_update.updates');

        return $this->download($filename, $updatesTxt . '.xlsx');
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadLegalUpdatesPDF(string $filename): BinaryFileResponse
    {
        /** @var string */
        $updatesTxt = __('notify.legal_update.updates');

        /** @var string */
        $updatesTxt = sprintf(
            '%s %s %s.pdf',
            $this->generateFilename($filename),
            $updatesTxt,
            now()->toDateString(),
        );

        return $this->download($filename, $updatesTxt . '.pdf');
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadAssessMetricsExcel(string $filename): BinaryFileResponse
    {
        /** @var string */
        $metricsTxt = __('assess.assess_metrics');

        return $this->download($filename, $metricsTxt . '.xlsx');
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadAssessResponsesExcel(string $filename): BinaryFileResponse
    {
        /** @var string */
        $responsesText = __('assess.assess_responses');

        return $this->download($filename, $responsesText . '.xlsx');
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadTasksExcel(string $filename): BinaryFileResponse
    {
        /** @var string $tasksTxt */
        $tasksTxt = __('tasks.task.tasks_export');

        return $this->genericLibryoExcelDownload($filename, $tasksTxt);
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadActionsExcel(string $filename): BinaryFileResponse
    {
        /** @var string $tasksTxt */
        $tasksTxt = __('actions.action_area.actions_export');

        return $this->genericLibryoExcelDownload($filename, $tasksTxt);
    }

    /**
     * @param string $type
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadEnablonExcel(string $type, string $filename): BinaryFileResponse
    {
        /** @var string $label */
        $label = __("corpus.enablon.types.{$type}_export");

        return $this->genericLibryoExcelDownload($filename, $label, explode('.', $filename)[1] ?? 'csv');
    }

    /**
     * @param string $filename
     * @param string $prefix
     * @param string $extension
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function genericLibryoExcelDownload(string $filename, string $prefix, string $extension = 'xlsx'): BinaryFileResponse
    {
        $stream = substr($filename, 0, strlen($filename) - 20);
        $stream = strlen($stream) > 1 ? " - {$stream}" : '';
        $date = now()->format('d M Y');

        $tasksTxt = "{$prefix}{$stream} - {$date}";

        return $this->download($filename, "{$tasksTxt}.{$extension}");
    }

    /**
     * @param string $filename
     *
     * @return BinaryFileResponse
     */
    public function downloadActionsStreamDataExcel(string $filename): BinaryFileResponse
    {
        /** @var string $prefix */
        $prefix = __('actions.dashboard.actions_dashboard_export');

        return $this->genericLibryoExcelDownload($filename, $prefix);
    }
}
