<?php

namespace App\Http\Services\Storage\My;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Services\Storage\OrganisationStorageService;
use App\Stores\Storage\FileStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Uploads Files to Drives module.
 */
class FileUploader
{
    public function __construct(
        protected OrganisationStorageService $organisationStorageService,
        protected FileStore $fileStore
    ) {
    }

    /**
     * @param Request                  $request
     * @param Folder                   $folder
     * @param Organisation|Norma|null $normaOrOrg
     *
     * @return Collection<File>
     */
    public function handleUpload(
        Request $request,
        Folder $folder,
        Organisation|Norma|null $normaOrOrg
    ): Collection {
        /** @var Collection<File> */
        $newFiles = (new File())->newCollection();

        /** @var UploadedFile|null */
        $file = $request->file('file');
        if (is_null($file)) {
            // @codeCoverageIgnoreStart
            /** @var array<UploadedFile>|null */
            $files = $request->file('files');
        // @codeCoverageIgnoreEnd
        } else {
            $files = [$file];
        }

        if (is_null($files)) {
            // @codeCoverageIgnoreStart
            return $newFiles;
            // @codeCoverageIgnoreEnd
        }

        $organisation = null;

        if ($normaOrOrg) {
            /** @var Organisation */
            $organisation = $normaOrOrg instanceof Organisation ? $normaOrOrg : $normaOrOrg->organisation;
        }

        /** @var User */
        $user = Auth::user();
        $title = $request->input('title') ?? null;
        $expiresAt = $request->input('expires_at') ?? null;

        // before uploading any, let's make sure they're all supported files
        foreach ($files as $file) {
            $this->fileIsSupportedMimeType($file);
        }

        foreach ($files as $file) {
            if ($organisation) {
                $this->canOrganisationStoreFile($organisation, $file);
            }

            $newFile = $this->fileStore->create($file, $user, $folder, $normaOrOrg->id ?? null, $title, $expiresAt);
            $newFiles->add($newFile);
        }

        return $newFiles;
    }

    /**
     * Throws an exception if file is not a supported mime type.
     *
     * @param UploadedFile $file
     *
     * @throws UnsupportedMediaTypeHttpException
     *
     * @return void
     */
    protected function fileIsSupportedMimeType(UploadedFile $file): void
    {
        if (!in_array(strtolower($file->getClientMimeType()), config('filesystems.supported.uploads'))) {
            /** @var string */
            $message = __('exceptions.unsupported_media_type');
            throw new UnsupportedMediaTypeHttpException($message);
        }
    }

    /**
     * Undocumented function.
     *
     * @param Organisation $organisation
     * @param UploadedFile $file
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return void
     */
    protected function canOrganisationStoreFile(Organisation $organisation, UploadedFile $file): void
    {
        if (!$this->organisationStorageService->canStoreDocument($organisation, $file)) {
            /** @var string */
            $message = __('exceptions.storage.organisation_no_storage');
            throw new UnprocessableEntityHttpException($message);
        }
    }
}
