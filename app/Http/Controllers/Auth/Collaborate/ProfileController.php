<?php

namespace App\Http\Controllers\Auth\Collaborate;

use App\Enums\Collaborators\DocumentType;
use App\Enums\Workflows\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserProfileRequest;
use App\Http\Requests\Collaborators\CollaboratorDocumentRequest;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\ProfileDocument;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use App\Stores\Collaborators\ProfileDocumentStore;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Get the current collaborator.
     *
     * @param array<string|int,mixed> $with
     * @param array<string|int,mixed> $withCount
     *
     * @return Collaborator
     */
    protected function currentCollaborator(array $with = [], array $withCount = []): Collaborator
    {
        $userId = Auth::id();

        /** @var Collaborator */
        return Collaborator::withScore()
            ->withCount($withCount)
            ->with($with)
            ->with(['ratingAverage.type'])
            ->find($userId);
    }

    /**
     * Get the collaborator profile.
     *
     * @param Request $request
     *
     * @return View
     */
    public function show(Request $request): View
    {
        $user = $this->currentCollaborator(['profile.languages'], ['completedTasks']);

        $tasks = Task::forActivity($user->id)
            ->when(request('task_type'), fn ($query) => $query->where('task_type_id', request('task_type')))
            ->paginate(30);

        $types = Task::where('user_id', $user->id)
            ->where('task_status', TaskStatus::done()->value)
            ->select(['task_type_id'])
            ->distinct();

        $types = TaskType::where('is_parent', false)->whereIn('id', $types)->pluck('name', 'id')->toArray();

        /** @var Route $route */
        $route = $request->route();

        // @codeCoverageIgnoreStart
        $tab = match ($route->getName()) {
            'collaborate.profile.email.show' => 'security',
            default => $request->get('tab', 'activities')
        };
        // @codeCoverageIgnoreEnd

        /** @var View */
        return view('pages.auth.collaborate.profile.profile')
            ->with([
                'user' => $user,
                'tasks' => $tasks,
                'taskTypes' => $types,
                'tab' => $tab,
            ]);
    }

    /**
     * Update the user details and redirect back.
     *
     * @param UserProfileRequest $request
     *
     * @return RedirectResponse
     */
    public function update(UserProfileRequest $request): RedirectResponse
    {
        $user = $this->currentCollaborator();

        /** @var Profile $profile */
        $profile = $user->profile;
        $profile->update($request->validated());

        $languages = collect();
        $proficiency = $request->collect('proficiency');
        $request->collect('languages')->each(function ($lang, $index) use ($languages, $proficiency) {
            $languages->push([
                'language_code' => $lang,
                'proficiency' => $proficiency->get($index),
            ]);
        });

        $profile->languages()->delete();
        $profile->languages()->createMany($languages->toArray());

        if ($request->hasFile('photo')) {
            /** @var UploadedFile $file */
            $file = $request->file('photo');
            $user->updateProfilePhoto($file);
        }

        $this->notifySuccessfulUpdate();

        $tab = $request->get('_tab');
        $tab = $tab ? "?tab={$tab}" : '';
        $target = redirect()->back(303)->getTargetUrl();

        if ($query = parse_url($target, PHP_URL_QUERY)) {
            $target = str_replace("?{$query}", '', $target);
        }

        /** @var RedirectResponse */
        return redirect("{$target}{$tab}");
    }

    /**
     * Get the given document.
     *
     * @param ProfileDocumentStore $store
     * @param Request              $request
     * @param string               $type
     *
     * @return Response
     */
    public function getDocument(ProfileDocumentStore $store, Request $request, string $type): Response
    {
        /** @var DocumentType $enum */
        $enum = DocumentType::fromField($type);

        $document = $store->get(null, $this->currentCollaborator()->profile, $enum);

        abort_unless((bool) $document, 404);

        /** @var ProfileDocument $document */
        $method = $request->routeIs('collaborate.collaborators.attachments.download') ? 'download' : 'stream';

        abort_unless((bool) $response = $document->attachment->{$method}(), 404);

        return $response;
    }

    /**
     * Update the provided document.
     *
     * @param ProfileDocumentStore        $store
     * @param CollaboratorDocumentRequest $request
     * @param string                      $type
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function updateDocument(ProfileDocumentStore $store, CollaboratorDocumentRequest $request, string $type): RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file($type);

        /** @var DocumentType $enum */
        $enum = DocumentType::fromField($type);

        $store->put($enum, $this->currentCollaborator()->profile, $file);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.profile.show', ['tab' => 'documents']);
    }
}
