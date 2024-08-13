<?php

namespace App\Traits\Storage;

use App\Actions\Storage\My\File\AfterFileUpload;
use App\Events\Auth\UserActivity\Folders\UploadedDocument;
use App\Http\Requests\System\FileUploadRequest;
use App\Http\Services\Storage\My\FileUploader;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait HandlesOrganisationFiles
{
    /**
     * Handle the given upload.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return array<string, mixed>
     */
    protected function handleUpload(): array
    {
        $uploader = app(FileUploader::class);
        $request = app(FileUploadRequest::class);

        // need to serve this via php-fpm, as this would otherwise set the max_execution_time globally
        ini_set('max_execution_time', '600');
        // setting the octane config wouldn't work either, as that is set at Swoole server start
        // Config::set('octane.max_execution_time', 600);

        $folderId = $request->input('folder_id');
        /** @var Folder $folder */
        $folder = Folder::findOrFail($folderId);
        /** @var ActiveLibryosManager $manager */
        $manager = app(ActiveLibryosManager::class);

        /** @var User $user */
        $user = Auth::user();

        $targetLibryo = $request->get('target_libryo_id') ? Libryo::whereKey($request->get('target_libryo_id'))->with('organisation')->userHasAccess($user)->firstOrFail() : null;

        if ($manager->isSingleMode()) {
            /** @var Libryo $libryoOrOrg */
            $libryoOrOrg = $manager->getActive();
            $organisation = $manager->getActiveOrganisation();
        } else {
            $libryoOrOrg = $manager->getActiveOrganisation();
            $organisation = $libryoOrOrg;
        }

        $organisation = $targetLibryo->organisation ?? $organisation;
        $libryoOrOrg = $targetLibryo ?? $libryoOrOrg;

        if ($folder->isLibryo()) {
            Gate::authorize('uploadFileForLibryo', [$folder, $libryoOrOrg]);
        }

        if ($folder->isOrganisation()) {
            Gate::authorize('uploadFileForOrganisation', [$folder, $targetLibryo->organisation ?? $organisation]);
            $files = $uploader->handleUpload($request, $folder, $organisation);
        } else {
            $files = $uploader->handleUpload($request, $folder, $libryoOrOrg);
        }

        foreach ($files as $file) {
            AfterFileUpload::run($request, $file, $user);
            event(new UploadedDocument($folder, $file, $user, $manager->getActive(), $manager->getActiveOrganisation()));
        }

        return [
            'files' => $files,
            'folder' => $folder,
            'organisation' => $organisation,
            'libryoOrOrg' => $libryoOrOrg,
        ];
    }
}
