<?php

namespace App\Http\Controllers\Actions\My;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\FilteredResource;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Http\Controllers\Assess\My\Traits\RedirectsTasksNotAvailable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveNormasManager;
use App\Traits\UsesBackButton;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Inertia\Response;

class ActionsTaskController extends Controller
{
    use DecodesHashids;
    use UsesBackButton;
    use RedirectsTasksNotAvailable;

    public function index(Request $request, string $view): View|Response
    {
        /** @var View|Response */
        return match ($view) {
            'calendar' => $this->renderCalendar($request),
            default => inertia('Actions/My/Task/IndexPage', ['active' => $view]),
        };
    }

    /**
     * @codeCoverageIgnore
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    protected function renderCalendar(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $organisation = $norma = null;
        $manager = app(ActiveNormasManager::class);
        if ($norma = $manager->getActive($user)) {
            $query = Task::forNorma($norma);
        } else {
            $organisation = $manager->getActiveOrganisation();
            $query = Task::forOrganisationUserAccess($organisation, $user);
        }

        $type = $request->hasAny(['show-month', 'show-year']) ? UserActivityType::changedCalendarDates() : UserActivityType::viewedCalendar();
        event(new GenericActivity($user, $type, null, $norma, $organisation));

        if ($request->get('search')) {
            event(new GenericActivity($user, UserActivityType::searchedTasks(), null, $norma, $organisation));
        }

        $filtered = $request->only(['statuses', 'type', 'priority', 'assignee', 'archived', 'overdue', 'project']);

        if (!empty($filtered)) {
            event(new FilteredResource($user, UserActivityType::filteredTasks(), $filtered, $norma, $organisation));
        }

        $month = $request->get('show-month', now()->month);
        $month = (int) $month;
        $month = $month > 12 || $month < 1 ? now()->month : $month;

        $year = $request->get('show-year', now()->year);
        $year = (int) $year;
        $year = $year > 3000 || $year < 2000 ? now()->year : $year;

        return view('pages.actions.my.action-area.tasks.calendar', [
            'filters' => $request->all(),
            'query' => $query->with(['assignee', 'project']),
            'showMonth' => $month,
            'showYear' => $year,
        ]);
    }

    /*
     * @return \Inertia\Response
     */
    public function show(string $task): Response
    {
        /** @var Task $task */
        $task = $this->decodeHash($task, Task::class);
        $this->authorize('view', $task);

        /** @var \Inertia\Response */
        return inertia('Actions/My/Task/ShowPage', [
            'task' => $task->id,
            'backButton' => $this->getPreviousUrl(route('my.actions.tasks.index', ['view' => 'list'])),
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, string $task): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Task $task */
        $task = $this->decodeHash($task, Task::class);
        $this->authorize('delete', $task);

        $manager = app(ActiveNormasManager::class);
        $norma = $manager->getActive();
        $this->redirectIfNoTasks($norma);

        $task->delete();

        if ($request->has('referer')) {
            /** @var RedirectResponse */
            return redirect($request->get('referer'));
        }

        $filters = $user->getTaskAppFilters();
        if (!empty($filters)) {
            return redirect()->route('my.actions.tasks.index', [...$filters, 'view' => 'list']);
        }

        return redirect()->route('my.actions.tasks.index', ['view' => 'list']);
    }
}
