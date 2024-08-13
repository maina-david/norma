<?php

namespace App\Http\Controllers\Storage\My;

use App\Enums\Auth\UserActivityType;
use App\Enums\Storage\My\FolderType;
use App\Events\Auth\UserActivity\Files\PreviewedFile;
use App\Events\Auth\UserActivity\Files\SearchedFiles;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\GetsLibryoAndOrganisation;
use App\Http\Controllers\Traits\PerformsActions;
use App\Http\Requests\Storage\My\FileRequest;
use App\Http\Requests\System\FileUploadRequest;
use App\Http\Services\Storage\My\FileUploader;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Customer\ActiveOrganisationManager;
use App\Services\Storage\MimeTypeManager;
use App\Stores\Ontology\UserTagStore;
use App\Stores\Storage\FileStore;
use App\Traits\Storage\HandlesOrganisationFiles;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FileController extends Controller
{
    use GetsLibryoAndOrganisation;
    use PerformsActions;
    use HandlesOrganisationFiles;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     * @param FileStore                 $fileStore
     * @param UserTagStore              $userTagStore
     * @param MimeTypeManager           $mimeTypeManager
     * @param FileUploader              $fileUploader
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
        protected FileStore $fileStore,
        protected UserTagStore $userTagStore,
        protected MimeTypeManager $mimeTypeManager,
        protected FileUploader $fileUploader,
    ) {
    }

    /**
     * @param File $file
     *
     * @return View|RedirectResponse
     */
    public function show(File $file): View|RedirectResponse
    {
        $allowedActions = [];
        /** @var User $user */
        $user = Auth::user();

        if ($user->can('update', $file)) {
            $allowedActions[] = ['icon' => 'pencil', 'action' => 'edit', 'label' => __('storage.drives.edit_information')];
            $allowedActions[] = ['icon' => 'retweet-alt', 'action' => 'replace', 'label' => __('actions.replace')];
            $allowedActions[] = ['icon' => 'folder-tree', 'action' => 'move', 'label' => __('actions.move')];
        }
        if ($user->can('delete', $file)) {
            $allowedActions[] = ['icon' => 'trash-alt', 'action' => 'delete', 'label' => __('actions.delete')];
        }

        [$libryo, $organisation] = $this->getActiveLibryoAndOrganisation();
        event(new PreviewedFile($file, $user, $libryo, $organisation));

        /** @var View */
        return view('pages.storage.my.file.show', [
            'file' => $file->load(['userTags', 'libryo']),
            'canPreview' => $file->isPreviewable(),
            'allowedActions' => $allowedActions,
            'acceptedUploads' => $this->mimeTypeManager->getAcceptedUploads(),
        ]);
    }

    /**
     * @param File $file
     *
     * @return Response
     */
    public function stream(File $file): Response
    {
        return $file->streamFile();
    }

    /**
     * @param File $file
     *
     * @return Response
     */
    public function download(File $file): Response
    {
        /** @var User */
        $user = Auth::user();
        [$libryo, $organisation] = $this->getActiveLibryoAndOrganisation();
        GenericActivity::dispatch($user, UserActivityType::downloadedDocument(), ['id' => $file->id], $libryo, $organisation);

        return $file->download(true);
    }

    /**
     * Bulk delete the files.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function bulkDelete(Request $request): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Collection<int, File> $files */
        $files = $this->filterActionInput($request, File::class);

        $folderId = $files->first()?->folder_id;

        $files->each(function (File $file) use ($user) {
            if ($user->can('delete', $file)) {
                $this->fileStore->delete($file);
            }
        });

        Session::flash('flash.message', __('actions.success'));

        return redirect()->route('my.drives.folders.show', ['folder' => $folderId]);
    }

    /**
     * @param Request $request
     * @param File    $file
     *
     * @return RedirectResponse|Response
     */
    public function destroy(Request $request, File $file): RedirectResponse|Response
    {
        $folderId = $file->folder?->id;
        $this->fileStore->delete($file);

        Session::flash('flash.message', __('actions.success'));

        // if the delete happened from a relation, e.g. deleting from a SAI response or Task
        // then send back the files-for turbo stream view
        if ($response = $this->respondWithRelatedFilesStream($request)) {
            return $response;
        }

        return redirect()->route('my.drives.folders.show', ['folder' => $folderId]);
    }

    /**
     * @param FileUploadRequest $request
     * @param File              $file
     *
     * @return RedirectResponse
     */
    public function replace(FileUploadRequest $request, File $file): RedirectResponse
    {
        // need to serve this via php-fpm, as this would otherwise set the max_execution_time globally
        ini_set('max_execution_time', '600');

        /** @var UploadedFile */
        $uploadedFile = $request->file('file');
        /** @var User */
        $user = Auth::user();
        $this->fileStore->replace($file, $uploadedFile, $user);

        return back();
    }

    /**
     * @param File $file
     *
     * @return View
     */
    public function edit(File $file): View
    {
        /** @var View */
        return view('pages.storage.my.file.edit', [
            'file' => $file->load('userTags'),
        ]);
    }

    /**
     * @param FileRequest $request
     * @param File        $file
     *
     * @return RedirectResponse
     */
    public function update(FileRequest $request, File $file): RedirectResponse
    {
        $data = $request->validated();

        $file->update($request->only(['title', 'expires_at']));

        event(new GenericActivityUsingAuth(UserActivityType::editedFileInformation(), ['id' => $file->id]));

        if (isset($data['user_tags']) && count($data['user_tags']) > 0) {
            /** @var ActiveLibryosManager $manager */
            $manager = app(ActiveLibryosManager::class);
            /** @var User $user */
            $user = Auth::user();
            $organisation = $manager->getActiveOrganisation();

            $tags = $this->userTagStore->createFromForm($data['user_tags'], $organisation, $user);
            $file->userTags()->sync($tags);
        }

        return redirect()->route('my.drives.files.show', ['file' => $file]);
    }

    /**
     * @param FileUploadRequest $request
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return Response
     */
    protected function upload(Request $request): Response
    {
        $processed = $this->handleUpload();

        // if the upload happened from a relation, e.g. attaching to a AI response or Task
        // then send back the files-for turbo stream view
        if ($response = $this->respondWithRelatedFilesStream($request)) {
            return $response;
        }

        /** @var Folder $folder */
        $folder = $processed['folder'];

        // the response when uploaded inside a folder
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.storage.my.file.files-list',
            'target' => 'files-in-folder',
            'files' => FolderType::organisation()->is($folder->folder_type)
                ? $folder->files()->with('folder')->forOrganisation($processed['organisation'])->paginate(config('modules.drives.files_per_page'))
                : $folder->files()->with('folder')->forLibryo($processed['libryoOrOrg'])->paginate(config('modules.drives.files_per_page')),
            'currentFolder' => $folder,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     * @param File    $file
     *
     * @return RedirectResponse
     */
    public function move(Request $request, File $file): RedirectResponse
    {
        $validated = $request->validate([
            'destination' => 'required|integer',
        ]);

        /** @var Folder */
        $folder = Folder::findOrFail($validated['destination']);

        $file->update(['folder_id' => $folder->id]);

        Session::flash('flash.message', __('actions.success'));

        return back();
    }

    /**
     * @param Request $request
     *
     * @return Response|null
     */
    protected function respondWithRelatedFilesStream(Request $request): ?Response
    {
        if ($relation = $request->input('relation')) {
            $streamController = app(FileStreamController::class);
            $relatedId = $request->input('related_id');
            switch ($relation) {
                case 'comment':
                    /** @var Comment */
                    $comment = Comment::find($relatedId);

                    return $streamController->forComment($comment);
                case 'assessment-item-response':
                    /** @var AssessmentItemResponse */
                    $aiResponse = AssessmentItemResponse::find($relatedId);

                    return $streamController->forAssessmentItemResponse($aiResponse);
                default:
                case 'assessment-item-response':
                    /** @var Task */
                    $task = Task::find($relatedId);

                    return $streamController->forTask($task);
            }
        }

        return null;
    }

    public function index(Request $request): View
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $libryo = $manager->getActive();
        $organisation = $manager->getActiveOrganisation();

        $baseQuery = File::where(function ($q) use ($organisation, $libryo, $request) {
            $q->where(function ($builder) use ($organisation, $libryo, $request) {
                if (!is_null($libryo)) {
                    $builder->forOrganisation($organisation);
                    $builder->orWhere(function ($q) use ($libryo) {
                        $q->forLibryo($libryo);
                    });
                } else {
                    /** @var \App\Models\Auth\User $user */
                    $user = $request->user();
                    $builder->allForOrganisationUserAccess($organisation, $user);
                }
            })->when($libryo, function ($query) use ($libryo) {
                $query->orWhere(function ($builder) use ($libryo) {
                    $builder->typeGlobal()->inGlobalDrive(null, $libryo);
                });
            });
        });

        /** @var User $user */
        $user = Auth::user();
        if ($request->has('search')) {
            event(new SearchedFiles($user, $libryo, $organisation));
        }

        /** @var View */
        return view('pages.storage.my.file.search', [
            'baseQuery' => $baseQuery->with(['libryo']),
            'subTitle' => $libryo ? $libryo->title : $organisation->title,
            'tableFields' => is_null($libryo) ? ['title_multi_stream'] : ['title'],
        ]);
    }
}
