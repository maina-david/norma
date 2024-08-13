<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Actions\Corpus\Work\ImportWorksFromSpreadsheet;
use App\Http\Controllers\Controller;
use App\Models\Workflows\Project;
use App\Services\TempFileManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class IngestIntoProjectController extends Controller
{
    /**
     * @param Project $project
     *
     * @return View
     */
    public function createImport(Project $project): View
    {
        $project->load(['location']);

        return view('pages.workflows.collaborate.project.import-by-spreadsheet', [
            'project' => $project,
            'sourceOptions' => $project->sources->pluck('title', 'id')->toArray(),
        ]);
    }

    /**
     * @param Request $request
     * @param Project $project
     *
     * @return RedirectResponse
     */
    public function importExcel(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'source_id' => ['required'],
            'location_id' => ['required'],
            'language_code' => ['required'],
            'file' => ['required', 'file'],
        ]);

        /** @var UploadedFile|null */
        $file = $request->file('file');
        $source_id = $request->input('source_id');

        // @codeCoverageIgnoreStart
        if (!$file) {
            // just a POC for now, so not using language files..
            $this->notifyErrorMessage('Please select a file');

            return back();
        }
        if (!$project->board) {
            // just a POC for now, so not using language files..
            $this->notifyErrorMessage('Project needs to have a default workflow set');

            return back();
        }
        // @codeCoverageIgnoreEnd

        $tmpFilePath = app(TempFileManager::class)->storeWithRandomName($file);

        ImportWorksFromSpreadsheet::dispatch(
            $tmpFilePath,
            $project->id,
            $source_id,
            $request->get('location_id'),
            $request->get('language_code'),
            auth()->user()?->id
        );

        // just a POC for now, so not using language files..
        $this->notifyGeneralSuccess('The import has been queued and will be processed ASAP');

        return back();
    }
}
