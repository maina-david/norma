<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Models\Auth\User;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ToCPageController extends WorkflowTaskController
{
    /**
     * Load the ToC Page.
     *
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @throws AuthorizationException
     *
     * @return View
     */
    public function show(WorkExpression $expression, ?Task $task = null): View
    {
        $this->authorize('editToC', [$expression, $task]);

        $this->removePreviewing($expression->id);
        $this->switchTask($task);

        /** @var User $user */
        $user = Auth::user();

        /** @var View */
        return view('pages.corpus.collaborate.toc.index', [
            'expression' => $expression,
            'task' => $task,
            'chainTasks' => Task::siblings($user, $expression, $task),
        ]);
    }
}
