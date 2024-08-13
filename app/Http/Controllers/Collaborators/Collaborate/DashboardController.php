<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Enums\Workflows\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Collaborators\Collaborator;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Get the dashboard view.
     *
     * @param Request $request
     *
     * @return View|MultiplePendingTurboStreamResponse
     */
    public function index(Request $request): View|MultiplePendingTurboStreamResponse
    {
        $sort = $request->get('sort', '-id');
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $sort = $sortDirection === 'desc' ? substr($sort, 1) : $sort;

        /** @var Collaborator $collaborator */
        $collaborator = Collaborator::withCount(['completedTasks', 'todoTasks', 'inReviewTasks', 'inProgressTasks'])
            ->whereKey(Auth::id())
            ->firstOrFail();

        $defaultStatus = [
            TaskStatus::todo()->value,
            TaskStatus::inProgress()->value,
        ];
        $filter = $request->only(['status', 'priority', 'types', 'jurisdiction', 'domains']);
        $filter['status'] = $request->get('assignment') == 1 ? [TaskStatus::todo()->value] : ($filter['status'] ?? $defaultStatus);

        $tasks = Task::filter($filter)
            ->whereIn('group_id', $collaborator->groups()->select(['id']))
            ->when($request->get('assignment') == 1, fn ($query) => $query->whereNull('user_id'))
            ->when($request->get('assignment') == 0, fn ($query) => $query->where('user_id', $collaborator->id))
            ->whereIn('task_type_id', TaskType::accessible()->pluck('id')->toArray())
            ->orderBy($sort, $sortDirection)
            ->paginate(20);

        if (!$request->header('turbo-frame')) {
            /** @var View */
            return view('pages.collaborators.collaborate.dashboard.index', [
                'collaborator' => $collaborator,
                'tasks' => $tasks,
            ]);
        }

        return multipleTurboStreamResponse([
            singleTurboStreamResponse('task-filters', 'update')
                ->view('pages.collaborators.collaborate.dashboard.partials.filter-form', [
                    'collaborator' => $collaborator,
                    'tasks' => $tasks,
                ]),
            singleTurboStreamResponse('task-listing', 'update')
                ->view('pages.collaborators.collaborate.dashboard.partials.task-listing', [
                    'collaborator' => $collaborator,
                    'tasks' => $tasks,
                ]),
        ]);
    }
}
