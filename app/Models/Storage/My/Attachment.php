<?php

namespace App\Models\Storage\My;

use App\Enums\Storage\FileType;
use App\Models\AbstractModel;
use App\Stores\Storage\FileSystemStore;
use App\Traits\InteractsWithStorage;
use Exception;
use Illuminate\Http\UploadedFile;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperAttachment
 */
class Attachment extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use InteractsWithStorage;

    protected $table = 'attachments';

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Mutators
    |--------------------------------------------------------------------------

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the path to the item in storage.
     *
     * @return string
     */
    protected function filePath(): string
    {
        return $this->path;
    }

    /**
     * Get the name of the file.
     *
     * @return string
     */
    protected function fileName(): string
    {
        return $this->name;
    }

    /**
     * Get the mime type of the file.
     *
     * @return string
     */
    protected function fileMimeType(): string
    {
        return $this->mime_type;
    }

    /**
     * Get the file type of the file.
     *
     * @return FileType
     */
    protected function getFileType(): FileType
    {
        return FileType::my();
    }

    /**
     * Store the uploaded file and return an updated instance of the implementer
     * of this trait.
     *
     * @param UploadedFile $file
     *
     * @throws Exception
     *
     * @return self
     */
    public function saveFile(UploadedFile $file): self
    {
        $path = app(FileSystemStore::class)->store($file, $file->hashName(), '');

        if ($path) {
            $this->fileSaved($file, $path);
        }

        return $this;
    }

    /**
     * Handle the changes required after the file has been saved in storage.
     *
     * @param UploadedFile $file
     * @param string       $path
     *
     * @return void
     */
    protected function fileSaved(UploadedFile $file, string $path): void
    {
        $this->path = $path;
        $this->name = $file->getClientOriginalName();
        $this->extension = $file->getClientOriginalExtension();
        $this->mime_type = $file->getClientMimeType();
        $this->size = $file->getSize();
        $this->save();
    }
}
