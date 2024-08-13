<?php

namespace App\Http\Controllers\Storage\My\Settings;

use App\Actions\Storage\My\File\AfterFileUpload;
use App\Enums\Storage\My\FolderType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\My\Settings\FolderRequest;
use App\Http\Requests\Storage\My\Settings\GlobalFolderRequest;
use App\Http\Services\Storage\My\FileUploader;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Storage\FileLegalDomainStore;
use App\Stores\Storage\FileLocationStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\ComponentAttributeBag;

class FolderStreamController extends Controller
{
    /**
     * @param Folder $folder
     * @param string $hideOrShow
     *
     * @return Response
     */
    public function hideOrShowLibryoCreatedFolders(Folder $folder, string $hideOrShow): Response
    {
        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);
        /** @var Organisation $organisation */
        $organisation = $manager->getActive();
        if ($hideOrShow === 'hide') {
            $organisation->hideFolderInSettings($folder);
        } else {
            $organisation->showFolderInSettings($folder);
        }

        /** @var FolderType */
        $folderType = FolderType::fromValue($folder->folder_type);
        $view = $this->getLibryoCreatedFoldersView($folderType);

        return turboStreamResponse($view);
    }

    /**
     * @param FolderType $folderType
     *
     * @return View
     */
    private function getLibryoCreatedFoldersView(FolderType $folderType): View
    {
        /** @var View */
        return view('streams.single-component-partial', [
            'component' => 'storage.my.folder.settings.libryo-folder-setup',
            'target' => 'settings-drives-libryo-specific-folders-' . $folderType->value,
            'attributes' => new ComponentAttributeBag([
                'folderType' => $folderType->value,
            ]),
        ]);
    }

    /**
     * @param int         $folderType
     * @param Folder|null $folder
     *
     * @return Response
     */
    public function indexOrganisationFolder(int $folderType, ?Folder $folder = null): Response
    {
        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);
        /** @var Organisation $organisation */
        $organisation = $manager->getActive();

        if (!is_null($folder)) {
            $this->authorize('manageOrganisationFolder', [$folder, $organisation]);
        }

        /** @var FolderType */
        $folderType = FolderType::fromValue($folderType);

        return turboStreamResponse($this->getOrganisationFolderSetupView($folderType, $folder?->id));
    }

    public function storeOrganisationFolder(FolderRequest $request, ?Folder $parentFolder = null): Response
    {
        return $this->handleStoreOrUpdateOrganisationFolder($request, $parentFolder);
    }

    public function updateOrganisationFolder(FolderRequest $request, Folder $folder, ?Folder $parentFolder = null): Response
    {
        return $this->handleStoreOrUpdateOrganisationFolder($request, $parentFolder, $folder);
    }

    private function handleStoreOrUpdateOrganisationFolder(
        FolderRequest $request,
        ?Folder $parentFolder = null,
        ?Folder $updateFolder = null,
    ): Response {
        $validated = $request->validated();

        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);
        /** @var Organisation $organisation */
        $organisation = $manager->getActive();
        if ($parentFolder && $parentFolder->organisation_id !== $organisation->id) {
            // @codeCoverageIgnoreStart
            abort(403);
            // @codeCoverageIgnoreEnd
        }

        /** @var User $user */
        $user = Auth::user();

        $validated['folder_parent_id'] = $parentFolder?->id;
        $validated['organisation_id'] = $organisation->id;
        $validated['author_id'] = $user->id;
        if (is_null($updateFolder)) {
            // store
            Folder::create($validated);
        } else {
            // update
            $updateFolder->update($validated);
        }

        /** @var FolderType */
        $folderType = FolderType::fromValue((int) $validated['folder_type']);
        $view = $this->getOrganisationFolderSetupView($folderType, $parentFolder?->id);

        return turboStreamResponse($view);
    }

    /**
     * @param FolderType $folderType
     * @param int|null   $folderId
     *
     * @return View
     */
    private function getOrganisationFolderSetupView(FolderType $folderType, ?int $folderId): View
    {
        /** @var View */
        return view('streams.single-component-partial', [
            'component' => 'storage.my.folder.settings.organisation-folder-setup',
            'target' => 'settings-drives-setup-organisation-folders-' . $folderType->value,
            'attributes' => new ComponentAttributeBag([
                'folderType' => $folderType->value,
                'folderId' => $folderId,
            ]),
        ]);
    }

    /**
     * @param Folder $folder
     *
     * @return Response
     */
    public function destroyOrganisationFolder(Folder $folder): Response
    {
        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);
        /** @var Organisation $organisation */
        $organisation = $manager->getActive();
        $this->authorize('manageOrganisationFolder', [$folder, $organisation]);

        $parentFolderId = $folder->folder_parent_id;

        $folder->delete();

        /** @var FolderType */
        $folderType = FolderType::fromValue($folder->folder_type);
        $view = $this->getOrganisationFolderSetupView($folderType, $parentFolderId);

        return turboStreamResponse($view);
    }

    /**
     * @param int|null $folder
     *
     * @return Response
     */
    public function indexGlobalFolder(?int $folder = null): Response
    {
        if (!is_null($folder)) {
            /** @var Folder $folder */
            $folder = Folder::typeGlobal()->findOrFail($folder);
        }

        /** @var View $view */
        $view = view('streams.single-component-partial', [
            'component' => 'storage.my.folder.settings.global-folder-setup',
            'target' => 'settings-drives-setup-global-folders',
            'attributes' => new ComponentAttributeBag([
                'folderType' => FolderType::global()->value,
                'folderId' => $folder?->id,
            ]),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * @param GlobalFolderRequest $request
     * @param int|null            $parentFolder
     *
     * @return Response
     */
    public function storeGlobalFolder(GlobalFolderRequest $request, ?int $parentFolder = null): Response
    {
        if (!is_null($parentFolder)) {
            /** @var Folder $parentFolder */
            $parentFolder = Folder::typeGlobal()->findOrFail($parentFolder);
        }

        $validated = $request->validated();

        $validated['folder_parent_id'] = $parentFolder?->id;
        $validated['folder_type'] = FolderType::global()->value;
        $validated['author_id'] = Auth::id();
        Folder::create($validated);

        return $this->indexGlobalFolder($parentFolder?->id);
    }

    /**
     * @param GlobalFolderRequest $request
     * @param int                 $folder
     *
     * @return Response
     */
    public function updateGlobalFolder(GlobalFolderRequest $request, int $folder): Response
    {
        /** @var Folder $folder */
        $folder = Folder::typeGlobal()->findOrFail($folder);

        $folder->update($request->validated());

        return $this->indexGlobalFolder($folder->folder_parent_id);
    }

    /**
     * @param int $folder
     *
     * @return Response
     */
    public function destroyGlobalFolder(int $folder): Response
    {
        /** @var Folder $folder */
        $folder = Folder::typeGlobal()->findOrFail($folder);

        $folder->delete();

        return $this->indexGlobalFolder($folder->folder_parent_id);
    }

    /**
     * @param Request $request
     * @param int     $folder
     *
     * @return Response
     */
    public function storeGlobalFile(Request $request, int $folder): Response
    {
        /** @var Folder $folder */
        $folder = Folder::typeGlobal()->findOrFail($folder);

        // need to serve this via php-fpm, as this would otherwise set the max_execution_time globally
        ini_set('max_execution_time', '600');

        $files = app(FileUploader::class)->handleUpload($request, $folder, null);

        /** @var User $user */
        $user = Auth::user();

        $fileDomain = app(FileLegalDomainStore::class);
        $fileLocation = app(FileLocationStore::class);

        $domains = $request->collect('legal_domains');
        $locations = $request->collect('locations');

        foreach ($files as $file) {
            $fileDomain->attachLegalDomains($file, $domains);
            $fileLocation->attachLocations($file, $locations);

            AfterFileUpload::run($request, $file, $user);
        }

        return $this->indexGlobalFolder($folder->id);
    }

    /**
     * @param int $file
     *
     * @return Response
     */
    public function destroyGlobalFile(int $file): Response
    {
        /** @var File $file */
        $file = File::typeGlobal()->findOrFail($file);

        $file->delete();

        return $this->indexGlobalFolder($file->folder_id);
    }
}
