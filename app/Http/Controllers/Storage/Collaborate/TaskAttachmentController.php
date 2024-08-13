<?php

namespace App\Http\Controllers\Storage\Collaborate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\Collaborate\TaskAttachmentRequest;
use App\Models\Storage\Collaborate\Attachment;
use App\Models\Workflows\Pivots\TaskAttachment;
use App\Models\Workflows\Task;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

use function HotwiredLaravel\TurboLaravel\dom_id;

class TaskAttachmentController extends Controller
{
    /**
     * Get the listing of the attachments.
     *
     * @param Request $request
     * @param Task    $task
     *
     * @return mixed
     */
    public function index(Request $request, Task $task): mixed
    {
        abort_unless($request->isForTurboFrame(), 404);

        $task->load(['attachments', 'parent.attachments']);

        return singleTurboStreamResponse(dom_id($task, 'attachments'))
            ->view('partials.storage.collaborate.attachment.listing', [
                'attachments' => $task->attachments->concat($task->parent->attachments ?? []),
                'task' => $task,
            ]);
    }

    /**
     * Display the attachment.
     *
     * @param Task       $task
     * @param Attachment $attachment
     *
     * @return Response
     */
    public function show(Task $task, Attachment $attachment): Response
    {
        $attached = TaskAttachment::where('attachment_id', $attachment->id)
            ->where(function ($query) use ($task) {
                $query->where('task_id', $task->id)
                    ->when($task->parent_task_id, fn ($builder) => $builder->OrWhere('task_id', $task->parent_task_id));
            })
            ->exists();

        abort_unless($attached, 404);

        return $attachment->stream();
    }

    /**
     * Create a new attachment.
     *
     * @param TaskAttachmentRequest $request
     * @param Task                  $task
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function store(TaskAttachmentRequest $request, Task $task): RedirectResponse
    {
        $attachment = new Attachment();

        /** @var UploadedFile $file */
        $file = $request->file('file');

        $attachment->saveFile($file);

        $task->attachments()->attach($attachment->id);

        return redirect()->route('collaborate.task.attachments.index', ['task' => $task->id]);
    }

    /**
     * Delete the attachment.
     *
     * @param Task       $task
     * @param Attachment $attachment
     *
     * @return RedirectResponse
     */
    public function destroy(Task $task, Attachment $attachment): RedirectResponse
    {
        abort_unless($task->attachments()->whereKey($attachment->id)->exists(), 404);

        $attachment->delete();

        return redirect()->route('collaborate.task.attachments.index', ['task' => $task->id]);
    }
}
