<?php

namespace App\Traits\Storage;

use App\Actions\Storage\My\File\AfterFileUpload;
use App\Events\Auth\UserActivity\Folders\UploadedDocument;
use App\Http\Requests\System\FileUploadRequest;
use App\Http\Services\Storage\My\FileUploader;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Storage\My\Folder;
use App\Services\Customer\ActiveNormasManager;
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
        /** @var ActiveNormasManager $manager */
        $manager = app(ActiveNormasManager::class);

        /** @var User $user */
        $user = Auth::user();

        $targetNorma = $request->get('target_norma_id') ? Norma::whereKey($request->get('target_norma_id'))->with('organisation')->userHasAccess($user)->firstOrFail() : null;

        if ($manager->isSingleMode()) {
            /** @var Norma $normaOrOrg */
            $normaOrOrg = $manager->getActive();
            $organisation = $manager->getActiveOrganisation();
        } else {
            $normaOrOrg = $manager->getActiveOrganisation();
            $organisation = $normaOrOrg;
        }

        $organisation = $targetNorma->organisation ?? $organisation;
        $normaOrOrg = $targetNorma ?? $normaOrOrg;

        if ($folder->isNorma()) {
            Gate::authorize('uploadFileForNorma', [$folder, $normaOrOrg]);
        }

        if ($folder->isOrganisation()) {
            Gate::authorize('uploadFileForOrganisation', [$folder, $targetNorma->organisation ?? $organisation]);
            $files = $uploader->handleUpload($request, $folder, $organisation);
        } else {
            $files = $uploader->handleUpload($request, $folder, $normaOrOrg);
        }

        foreach ($files as $file) {
            AfterFileUpload::run($request, $file, $user);
            event(new UploadedDocument($folder, $file, $user, $manager->getActive(), $manager->getActiveOrganisation()));
        }

        return [
            'files' => $files,
            'folder' => $folder,
            'organisation' => $organisation,
            'normaOrOrg' => $normaOrOrg,
        ];
    }
}
