<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Tasks\Task;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TaskActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function indexForTask(Task $task): Response
    {
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.tasks.task-activity.for-task-index',
            'target' => 'activities-for-task-' . $task->id,
            'activities' => $task->activities()
                ->with(['user'])
                ->orderByDesc('created_at')
                ->simplePaginate(25),
        ]);

        return turboStreamResponse($view);
    }
}
