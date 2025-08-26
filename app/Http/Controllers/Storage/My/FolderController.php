<?php

namespace App\Http\Controllers\Storage\My;

use App\Enums\Customer\NormaSwitcherMode;
use App\Enums\Storage\My\FolderType;
use App\Events\Auth\UserActivity\Folders\FolderViewed;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Customer\ActiveOrganisationManager;
use App\Services\Storage\FolderLists;
use App\Services\Storage\MimeTypeManager;
use App\Services\Storage\OrganisationStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    /**
     * @param ActiveOrganisationManager  $activeOrganisationManager
     * @param OrganisationStorageService $organisationStorageService
     * @param MimeTypeManager            $mimeTypeManager
     * @param FolderLists                $folderLists
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
        protected OrganisationStorageService $organisationStorageService,
        protected MimeTypeManager $mimeTypeManager,
        protected FolderLists $folderLists,
    ) {
    }

    /**
     * @return View
     */
    public function root(): View
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $mode = $manager->getMode();

        /** @var User $user */
        $user = Auth::user();

        $norma = $manager->getActive($user);
        $organisation = $manager->getActiveOrganisation();

        $storageAllocation = $this->organisationStorageService->getAllocation($organisation);

        $percentage = round(($storageAllocation['used_bytes'] / $storageAllocation['total_bytes']) * 100);
        $storageColor = 'positive';
        if ($percentage > 50) {
            // @codeCoverageIgnoreStart
            $storageColor = 'warning';
            // @codeCoverageIgnoreEnd
        }
        if ($percentage > 75) {
            // @codeCoverageIgnoreStart
            $storageColor = 'negative';
            // @codeCoverageIgnoreEnd
        }

        /** @var View */
        return view('pages.storage.my.drives', [
            'singleMode' => NormaSwitcherMode::single()->is($mode),
            'norma' => $norma,
            'organisation' => $organisation,
            'storageAllocation' => $storageAllocation,
            'percentage' => $percentage,
            'storageColor' => $storageColor,
            'subTitle' => $norma ? $norma->title : $organisation->title,
        ]);
    }

    /**
     * @param Folder $folder
     *
     * @return View|RedirectResponse
     */
    public function show(Folder $folder): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Norma|null */
        $norma = $manager->getActive();

        switch ($folder->folder_type) {
            case FolderType::norma()->value:
                $response = $this->forNorma($folder->children(), $folder);
                break;
            case FolderType::organisation()->value:
                $response = $this->forOrganisation($folder->children(), $folder);
                break;
            default:
                $response = $this->forGlobal($folder->children()->inGlobalDrive($norma ?? null), $folder);
                break;
        }

        event(new FolderViewed($folder, $user, $norma, $manager->getActiveOrganisation()));

        return $response;
    }

    /**
     * @param string $type
     *
     * @return View|RedirectResponse
     */
    public function showRoot(string $type): View|RedirectResponse
    {
        $query = Folder::forParent()->orderBy('title');
        if ($type === 'norma') {
            return $this->forNorma($query);
        }
        if ($type === 'organisation') {
            return $this->forOrganisation($query);
        }

        $query->typeGlobal();

        return $this->forGlobal($query);
    }

    /**
     * @param Builder|QueryBuilder|Relation $query
     * @param Folder|null|null              $folder
     *
     * @return View|RedirectResponse
     */
    protected function forNorma(Builder|QueryBuilder|Relation $query, ?Folder $folder = null): View|RedirectResponse
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $mode = $manager->getMode();
        if (NormaSwitcherMode::all()->is($mode)) {
            // @codeCoverageIgnoreStart
            return redirect()->route('my.drives.folders.show.root', ['type' => 'organisation']);
            // @codeCoverageIgnoreEnd
        }
        /** @var User $user */
        $user = Auth::user();
        /** @var Norma */
        $norma = $manager->getActive($user);
        $emptyQuery = $query->forNorma($norma)
            ->clone()
            ->emptyForNorma($norma->id);

        /** @var string */
        $title = is_null($folder) ? __('storage.drives.norma_drive', ['title' => $norma->title]) : $folder->title;

        return $this->showFolderView(
            $title,
            $query->notEmptyForNorma($norma->id)->get(),
            $emptyQuery->get(),
            $folder?->files()->forNorma($norma)->with('folder')->withPermissions($user)->paginate(config('modules.drives.files_per_page')) ?? (new File())->whereKey(0)->paginate(config('modules.drives.files_per_page')),
            $folder,
            'primary',
            'norma',
        );
    }

    /**
     * @param Builder|QueryBuilder|Relation $query
     * @param Folder|null|null              $folder
     *
     * @return View|RedirectResponse
     */
    protected function forOrganisation(Builder|QueryBuilder|Relation $query, ?Folder $folder = null): View|RedirectResponse
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $mode = $manager->getMode();

        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        $emptyQuery = $query->forOrganisation($organisation)
            ->clone()
            ->emptyForOrganisation($organisation->id);

        /** @var User $user */
        $user = Auth::user();

        /** @var string */
        $title = is_null($folder) ? __('storage.drives.organisation_drive', ['title' => $organisation->title]) : $folder->title;

        return $this->showFolderView(
            $title,
            $query->notEmptyForOrganisation($organisation->id)->get(),
            $emptyQuery->get(),
            $folder?->files()->forOrganisation($organisation)->with('folder')->withPermissions($user)->paginate(config('modules.drives.files_per_page')) ?? (new File())->whereKey(0)->with('folder')->paginate(config('modules.drives.files_per_page')),
            $folder,
            'secondary',
            'organisation',
        );
    }

    /**
     * @param Builder|QueryBuilder|Relation $query
     * @param Folder|null                   $folder
     *
     * @return View|RedirectResponse
     */
    protected function forGlobal(Builder|QueryBuilder|Relation $query, ?Folder $folder = null): View|RedirectResponse
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $emptyQuery = $query->clone();

        /** @var User $user */
        $user = Auth::user();
        $norma = $manager->getActive($user);

        if (is_null($folder)) {
            /** @var LengthAwarePaginator<File> */
            $files = (new File())->whereKey(0)->with('folder')->paginate(config('modules.drives.files_per_page'));
        } else {
            /** @var LengthAwarePaginator<File> */
            $files = File::inGlobalDrive($folder, $norma)->with('folder')->paginate(config('modules.drives.files_per_page'));
        }

        /** @var string */
        $title = is_null($folder) ? __('storage.drives.shared_drive') : $folder->title;

        return $this->showFolderView(
            $title,
            $query->notEmpty()->get(),
            $emptyQuery->empty()->get(),
            $files,
            $folder,
            'norma-gray-500',
            'global',
        );
    }

    /**
     * @param string                     $title
     * @param Collection<Folder>         $folders
     * @param Collection<Folder>         $emptyFolders
     * @param LengthAwarePaginator<File> $files
     * @param Folder|null                $currentFolder
     * @param string                     $iconColor
     * @param string|null|null           $type
     *
     * @return View
     */
    protected function showFolderView(
        string $title,
        Collection $folders,
        Collection $emptyFolders,
        LengthAwarePaginator $files,
        ?Folder $currentFolder,
        string $iconColor = 'gray',
        ?string $type = null,
    ): View {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var User $user */
        $user = Auth::user();

        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive($user);
            /** @var Organisation */
            $organisation = $norma->organisation;
        } else {
            /** @var Organisation */
            $organisation = $this->activeOrganisationManager->getActive();
        }

        $customFolders = $organisation->settings['folders'] ?? [];
        $folders = $this->folderLists->toCustomFolderList($folders, $customFolders);
        $emptyFolders = $this->folderLists->toCustomFolderList($emptyFolders, $customFolders);

        $canUpload = false;
        $canManageGlobal = $type === 'global' && $user->can('my.storage.folder.global.manage');

        if ($currentFolder && ($type !== 'global' || $canManageGlobal)) {
            $canUpload = true;
        }

        /** @var Request */
        $request = request();

        $uploadRoute = route('my.drives.files.upload');

        /** @var View */
        return view('pages.storage.my.folder.show', [
            'type' => $type,
            'title' => $title,
            'folders' => $folders,
            'currentFolder' => $currentFolder,
            'files' => $files,
            'emptyFolders' => $emptyFolders,
            'customFolderNames' => $customFolders,
            'iconColor' => $iconColor,
            'canUpload' => $canUpload,
            'uploadRoute' => $uploadRoute,
            'acceptedUploads' => $this->mimeTypeManager->getAcceptedUploads(),
            'backRoute' => isset($currentFolder) && $currentFolder->folder_parent_id
                ? route('my.drives.folders.show', ['folder' => $currentFolder->folder_parent_id])
                : ($request->routeIs('my.drives.folders.show.root')
                    ? route('my.drives.root')
                    : route('my.drives.folders.show.root', ['type' => $type])),
        ]);
    }
}
