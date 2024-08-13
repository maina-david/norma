<?php

namespace App\Actions\Corpus\Work;

use App\Actions\Workflows\Task\CreateTasksForWorkInProject;
use App\Mail\Corpus\IngestionMissingActionAreas;
use App\Models\Auth\User;
use App\Models\Workflows\Project;
use App\Services\Corpus\IngestBySpreadsheet;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ImportWorksFromSpreadsheet
{
    use AsAction;

    public string $jobQueue = 'exports';
    public int $jobTries = 1;

    public function __construct(protected IngestBySpreadsheet $ingestBySpreadsheet)
    {
    }

    /**
     * A job to call importFromExcel in IngestBySpreadsheet and send out an email if there
     * are missing action areas.
     *
     * @param string   $tmpFilePath
     * @param int      $projectId
     * @param int      $sourceId
     * @param int      $locationId
     * @param string   $languageCode
     * @param int|null $importedByUserId
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     *
     * @return void
     */
    public function handle(
        string $tmpFilePath,
        int $projectId,
        int $sourceId,
        int $locationId,
        string $languageCode,
        ?int $importedByUserId = null,
    ): void {
        $file = Storage::get($tmpFilePath);
        $reader = new Xlsx();
        $localFile = storage_path('tmp') . uniqid();
        file_put_contents($localFile, $file);
        $spreadsheet = $reader->load($localFile);

        $project = Project::findOrFail($projectId);

        $report = $this->ingestBySpreadsheet->importFromExcel(
            $spreadsheet,
            $sourceId,
            $locationId,
            $languageCode
        );

        // for each work created in report create document and create tasks
        foreach ($report['works_created'] as $workId) {
            CreateTasksForWorkInProject::dispatch($workId, $project->id, $locationId);
        }

        if (!empty($report['missing_action_areas']) && $importedByUserId) {
            /** @var User $user */
            $user = User::find($importedByUserId);
            Mail::to($user->email)->send(new IngestionMissingActionAreas($report['missing_action_areas']));
        }

        if (file_exists($localFile)) {
            unlink($localFile);
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @param string   $tmpFilePath
     * @param int      $projectId
     * @param int      $sourceId
     * @param int      $locationId
     * @param string   $languageCode
     * @param int|null $importedByUserId
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     *
     * @return void
     */
    public function asJob(
        string $tmpFilePath,
        int $projectId,
        int $sourceId,
        int $locationId,
        string $languageCode,
        ?int $importedByUserId = null,
    ): void {
        $this->handle(
            $tmpFilePath,
            $projectId,
            $sourceId,
            $locationId,
            $languageCode,
            $importedByUserId
        );
    }
}
