<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Requests\Corpus\WorkFileDeleteRequest;
use App\Http\Requests\Corpus\WorkFileRequest;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use App\Services\Corpus\WorkExpressionContentManager;
use App\Services\Storage\WorkStorageProcessor;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentEditorController extends WorkflowTaskController
{
    /**
     * Load the document editor page.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return View
     */
    public function index(Request $request, WorkExpression $expression, ?Task $task = null): View
    {
        $this->removePreviewing($expression->id);
        $this->switchTask($task);

        /** @var User $user */
        $user = $request->user();

        $chainTasks = Task::siblings($user, $expression, $task);

        $volume = (int) $request->query('volume', '1');

        /** @var string $content */
        $content = __('corpus.work.no_preview_available');

        if ($volume <= $expression->volume) {
            $content = $expression->getVolume($volume) ?? $content;
        }

        $pagination = new LengthAwarePaginator([$content], $expression->volume, 1, $volume, [
            'pageName' => 'volume',
            'path' => route('collaborate.document-editor.index', ['expression' => $expression->id, 'task' => $task?->id]),
        ]);

        /** @var View */
        return view('pages.corpus.collaborate.document-editor.index', [
            'expression' => $expression,
            'task' => $task,
            'chainTasks' => $chainTasks,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Update the expression content.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     * @param int            $volume
     * @param Task|null      $task
     *
     * @return RedirectResponse
     */
    public function update(Request $request, WorkExpression $expression, int $volume, ?Task $task = null): RedirectResponse
    {
        $this->switchTask($task);

        $content = $request->get('content');

        app(WorkExpressionContentManager::class)->saveVolume($expression, $volume, $content);

        $this->notifySuccessfulUpdate();

        /** @var RedirectResponse */
        return redirect()->route('collaborate.document-editor.index', [
            'expression' => $expression->id,
            'task' => $task?->id,
            'volume' => $volume,
        ]);
    }

    /**
     * Get the file browser.
     *
     * @param WorkStorageProcessor $processor
     * @param WorkExpression       $expression
     *
     * @return PendingTurboStreamResponse
     */
    public function fileBrowser(WorkStorageProcessor $processor, WorkExpression $expression): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();

        /** @var Work $work */
        $work = Work::where('id', $expression->work_id)->firstOrFail();

        $images = $processor->getWorkFile($work);

        return singleTurboStreamResponse('document_file_browser')
            ->view('pages.corpus.collaborate.document-editor.partials.file-browser', [
                'expression' => $expression,
                'images' => $images,
            ]);
    }

    /**
     * Store the file for the given work.
     *
     * @param WorkFileRequest      $request
     * @param WorkStorageProcessor $processor
     * @param WorkExpression       $expression
     *
     * @return RedirectResponse
     */
    public function storeFile(WorkFileRequest $request, WorkStorageProcessor $processor, WorkExpression $expression): RedirectResponse
    {
        $this->abortNotTurbo();
        /** @var Work $work */
        $work = Work::where('id', $expression->work_id)->firstOrFail();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        $processor->storeWorkFile($work, $file);

        return redirect()->route('collaborate.document-editor.file-browser.show', ['expression' => $expression->id]);
    }

    /**
     * Store the file for the given work.
     *
     * @param WorkFileDeleteRequest $request
     * @param WorkStorageProcessor  $processor
     * @param WorkExpression        $expression
     *
     * @return RedirectResponse
     */
    public function deleteFile(WorkFileDeleteRequest $request, WorkStorageProcessor $processor, WorkExpression $expression): RedirectResponse
    {
        $this->abortNotTurbo();
        /** @var Work $work */
        $work = Work::where('id', $expression->work_id)->firstOrFail();

        $file = Str::afterLast($request->get('file'), '/');

        $processor->deleteWorkFile($work, $file);

        $this->notifyGeneralSuccess();

        return redirect()->route('collaborate.document-editor.file-browser.show', ['expression' => $expression->id]);
    }
}
