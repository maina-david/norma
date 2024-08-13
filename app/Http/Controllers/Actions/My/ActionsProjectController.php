<?php

namespace App\Http\Controllers\Actions\My;

use App\Events\Auth\UserActivity\Projects\ProjectCreated;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Requests\Tasks\TaskProjectRequest;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Tasks\TaskProject;
use App\Services\Customer\ActiveLibryosManager;
use App\Traits\Actions\UsesActionAreasInLibryo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionsProjectController extends Controller
{
    use DecodesHashids;
    use UsesActionAreasInLibryo;

    /**
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

        /** @var User */
        $user = Auth::user();

        $organisation = $manager->getActiveOrganisation();
        $query = TaskProject::forOrganisation($organisation);

        if (is_null($request->input('archived'))) {
            $query->active();
        }

        /** @var View */
        return view('pages.actions.my.action-area.projects.index', [
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
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('my.projects.index');
    }

    /**
     * @return View
     */
    public function create(): View
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

        /** @var View */
        return view('pages.actions.my.action-area.projects.create');
    }

    /**
     * @param string $project
     *
     * @return View
     */
    public function edit(string $project): View
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

        $project = $this->decodeHash($project, TaskProject::class);
        $this->authorize('update', $project);

        /** @var View */
        return view('pages.actions.my.action-area.projects.edit', [
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
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

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

        return redirect()->route('my.projects.index');
    }

    /**
     * @param TaskProjectRequest $request
     * @param TaskProject        $project
     *
     * @return RedirectResponse
     */
    public function update(TaskProjectRequest $request, TaskProject $project): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

        $data = $request->validated();

        $project->update($data);

        return redirect()->route('my.projects.index');
    }

    /**
     * @param TaskProject $project
     *
     * @return RedirectResponse
     */
    public function archive(TaskProject $project): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

        $this->authorize('archive', $project);

        $project->update(['archived' => true]);

        return redirect()->route('my.projects.index');
    }

    /**
     * @param TaskProject $project
     *
     * @return RedirectResponse
     */
    public function unarchive(TaskProject $project): RedirectResponse
    {
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $this->redirectIfNoActionAreas($libryo);

        $this->authorize('archive', $project);

        $project->update(['archived' => false]);

        return redirect()->route('my.projects.index');
    }
}
