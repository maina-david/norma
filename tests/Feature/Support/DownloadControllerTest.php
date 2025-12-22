<?php

namespace Tests\Feature\Support;

use App\Enums\Enablon\ExportType;
use App\Models\Customer\Norma;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class DownloadControllerTest extends MyTestCase
{
    /**
     * @return void
     */
    private function runDownloadTest(Norma $norma, string $routeName, array $params = []): void
    {
        Storage::fake();
        $filename = Str::random() . '.xlsx';
        $filename = "l---{$norma->id}---{$filename}";
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
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.requirements.excel';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadRequirementsPDF(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.requirements.pdf';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadLegalUpdatesExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.legal-updates.excel';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadLegalUpdatesPDF(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.legal-updates.pdf';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadAssessMetricsExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.assess.metrics.excel';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadTasksExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.tasks.excel';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadActionsExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.actions.excel';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadResponsesExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.assess.responses.excel';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadActionsStreamDataExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.actions.dashboard.excel';
        $this->runDownloadTest($norma, $routeName);
    }

    /**
     * @return void
     */
    public function testDownloadEnablonExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.downloads.download.enablon.excel';
        $this->runDownloadTest($norma, $routeName, ['type' => ExportType::REQUIREMENTS->value]);
    }
}
