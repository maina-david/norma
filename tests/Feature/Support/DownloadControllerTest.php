<?php

namespace Tests\Feature\Support;

use App\Enums\Enablon\ExportType;
use App\Models\Customer\Libryo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class DownloadControllerTest extends MyTestCase
{
    /**
     * @return void
     */
    private function runDownloadTest(Libryo $libryo, string $routeName, array $params = []): void
    {
        Storage::fake();
        $filename = Str::random() . '.xlsx';
        $filename = "l---{$libryo->id}---{$filename}";
        $testContent = 'Test content';
        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::put($path, $testContent);

        $response = $this->get(route($routeName, [...$params, 'filename' => $filename]))
            ->assertSuccessful();
        // temp file should be deleted after download
        Storage::assertMissing($path);

        // the deleteAfterSend doesn't seem to work in testing env, so have to clean up
        $localTmpPath = storage_path('app/tmp') . DIRECTORY_SEPARATOR . $filename . '_tmp';
        unlink($localTmpPath);
    }

    /**
     * @return void
     */
    public function testDownloadRequirementsExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.requirements.excel';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadRequirementsPDF(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.requirements.pdf';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadLegalUpdatesExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.legal-updates.excel';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadLegalUpdatesPDF(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.legal-updates.pdf';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadAssessMetricsExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.assess.metrics.excel';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadTasksExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.tasks.excel';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadActionsExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.actions.excel';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadResponsesExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.assess.responses.excel';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadActionsStreamDataExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.actions.dashboard.excel';
        $this->runDownloadTest($libryo, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadEnablonExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.downloads.download.enablon.excel';
        $this->runDownloadTest($libryo, $routeName, ['type' => ExportType::REQUIREMENTS->value]);
    }
}
