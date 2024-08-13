<?php

namespace App\Stores\Storage;

use App\Enums\Arachno\Parse\PdfProperty;
use App\Enums\Storage\My\FolderType;
use App\Models\Auth\User;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Services\Arachno\Parse\PdfExtractor;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class FileStore
{
    public function __construct(protected FileSystemStore $fileSystemStore)
    {
    }

    /**
     * @param UploadedFile $file
     * @param User         $author
     * @param Folder       $folder
     * @param string|null  $title
     * @param int|null     $entityId = null
     *
     * @return File
     **/
    public function create(
        UploadedFile $file,
        User $author,
        Folder $folder,
        ?int $entityId = null,
        ?string $title = null,
        ?string $expiresAt = null,
    ): File {
        $data = [];
        $fileName = $file->hashName();
        $directory = $folder->getStoragePath($entityId);

        $data['path'] = $this->fileSystemStore->store($file, $fileName, $directory);

        switch ($folder->folder_type) {
            case FolderType::organisation()->value:
                $data['organisation_id'] = $entityId;
                break;
            case FolderType::libryo()->value:
                $data['place_id'] = $entityId;
                break;
                // @codeCoverageIgnoreStart
            default:
                break;
                // @codeCoverageIgnoreEnd
        }

        $mime = $file->getClientMimeType();
        $description = null;

        if ($mime === 'application/pdf') {
            $description = app(PdfExtractor::class)->getProperty(PdfProperty::SUBJECT, $file->getContent());
        }

        $data = array_merge($data, [
            'title' => $title ?? str_replace([',', '/', '\\', ':'], '', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
            'description' => $description,
            'folder_id' => $folder->id,
            'folder_type' => $folder->folder_type,
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $mime,
            'size' => $file->getSize(),
            'author_id' => $author->id,
            'expires_at' => $expiresAt,
        ]);

        $newModel = new File($data);
        $newModel->save();

        return $newModel;
    }

    /**
     * Replaces the file and it's attributes.
     *
     * @param File         $file
     * @param UploadedFile $uploadedFile
     * @param User         $author
     *
     * @return File
     **/
    public function replace(
        File $file,
        UploadedFile $uploadedFile,
        User $author
    ): File {
        $data = [];
        $directory = $file->folder?->getStoragePath($file->place_id ?? $file->organisation_id ?? null) ?? '';

        $fileName = $uploadedFile->hashName();
        $data['path'] = $this->fileSystemStore->store($uploadedFile, $fileName, $directory);

        $mime = $uploadedFile->getClientMimeType();
        $description = $file->description;

        if ($mime === 'application/pdf') {
            $description = app(PdfExtractor::class)->getProperty(PdfProperty::SUBJECT, $uploadedFile->getContent());
        }

        $data = array_merge($data, [
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'mime_type' => $mime,
            'description' => $description,
            'size' => $uploadedFile->getSize(),
            'author_id' => $author->id,
            'replaced_at' => Carbon::now(),
        ]);

        $this->fileSystemStore->destroyFileInStorage($file->path, $file->mime_type);

        $file->update($data);

        return $file;
    }

    /**
     * @param File $file
     *
     * @return void
     */
    public function delete(File $file): void
    {
        $file->delete();
    }

    /**
     * @param File $file
     *
     * @return void
     */
    // public function hardDelete(File $file): void
    // {
    //     $this->fileSystemStore->destroyFileInStorage($file->path, $file->mime_type);
    //     $file->forceDelete();
    // }
}
