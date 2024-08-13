<?php

namespace App\Http\Controllers\Tasks\My;

use App\Events\Auth\UserActivity\Projects\ProjectCreated;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Requests\Tasks\TaskProjectRequest;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Tasks\TaskProject;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskProjectController extends Controller
{
    use DecodesHashids;

    /**
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User */
        $user = Auth::user();

        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        $query = TaskProject::forOrganisation($organisation);

        if (is_null($request->input('archived'))) {
            $query->active();
        }

        /** @var View */
        return view('pages.tasks.my.task-project.index', [
            'query' => $query->with(['author']),
        ]);
    }

    /**
     * @param TaskProject $project
     *
     * @return RedirectResponse
     */
    public function destroy(TaskProject $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('my.tasks.task-projects.index');
    }

    /**
     * @return View
     */
    public function create(): View
    {
        /** @var View */
        return view('pages.tasks.my.task-project.create');
    }

    /**
     * @param string $project
     *
     * @return View
     */
    public function edit(string $project): View
    {
        $project = $this->decodeHash($project, TaskProject::class);
        $this->authorize('update', $project);

        /** @var View */
        return view('pages.tasks.my.task-project.edit', [
            'project' => $project,
        ]);
    }

    /**
     * @param TaskProjectRequest $request
     *
     * @return RedirectResponse
     */
    public function store(TaskProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $manager = app(ActiveLibryosManager::class);
        /** @var User */
        $user = Auth::user();

        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        $data['author_id'] = $user->id;
        $data['organisation_id'] = $organisation->id;

        $project = TaskProject::create($data);

        event(new ProjectCreated($project, $user, null, $organisation));

        return redirect()->route('my.tasks.task-projects.index');
    }

    /**
     * @param TaskProjectRequest $request
     * @param TaskProject        $project
     *
     * @return RedirectResponse
     */
    public function update(TaskProjectRequest $request, TaskProject $project): RedirectResponse
    {
        $data = $request->validated();

        $project->update($data);

        return redirect()->route('my.tasks.task-projects.index');
    }

    /**
     * @param TaskProject $project
     *
     * @return RedirectResponse
     */
    public function archive(TaskProject $project): RedirectResponse
    {
        $project->update(['archived' => true]);

        return redirect()->route('my.tasks.task-projects.index');
    }

    /**
     * @param TaskProject $project
     *
     * @return RedirectResponse
     */
    public function unarchive(TaskProject $project): RedirectResponse
    {
        $project->update(['archived' => false]);

        return redirect()->route('my.tasks.task-projects.index');
    }
}
