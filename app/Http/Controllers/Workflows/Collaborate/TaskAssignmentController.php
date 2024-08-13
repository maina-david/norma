<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Controller;
use App\Mail\Workflows\TaskApplicationCreatedEmail;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TaskAssignmentController extends Controller
{
    /**
     * Assign the task to yourself.
     *
     * @param Task $task
     *
     * @return RedirectResponse
     */
    public function assignSelf(Task $task): RedirectResponse
    {
        if (!$task->user_id) {
            $task->update(['user_id' => Auth::id()]);

            $this->notifyGeneralSuccess();
        }

        return redirect()->route('collaborate.tasks.show', ['task' => $task->id]);
    }

    /**
     * Assign the task to yourself.
     *
     * @param Task $task
     *
     * @return RedirectResponse
     */
    public function removeFromSelf(Task $task): RedirectResponse
    {
        if ($task->user_id === Auth::id()) {
            $task->update(['user_id' => null]);

            $this->notifyGeneralSuccess();
        }

        return redirect()->route('collaborate.tasks.show', ['task' => $task->id]);
    }

    /**
     * Create a new task application for the given task.
     *
     * @param Task $task
     *
     * @return RedirectResponse
     */
    public function store(Task $task): RedirectResponse
    {
        if (!TaskApplication::where('user_id', Auth::id())->where('task_id', $task->id)->first()) {
            /** @var TaskApplication $application */
            $application = TaskApplication::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
            ]);

            /** @var array<string> */
            $emails = config('collaborate.emails.team_details_change');

            Mail::to($emails)->send(new TaskApplicationCreatedEmail($application));
        }

        /** @var string */
        $msg = __('workflows.task.assignment.application_submitted');
        $this->notifyGeneralSuccess($msg);

        return redirect()->route('collaborate.dashboard');
    }

    /**
     * Delete the task application.
     *
     * @param Task $task
     *
     * @return RedirectResponse
     */
    public function destroy(Task $task): RedirectResponse
    {
        /** @var TaskApplication|null $application */
        $application = TaskApplication::where('user_id', Auth::id())->where('task_id', $task->id)->first();

        if ($application) {
            $application->delete();
        }

        $this->notifyGeneralSuccess();

        return redirect()->route('collaborate.dashboard');
    }
}
