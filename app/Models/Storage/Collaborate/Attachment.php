<?php

namespace App\Models\Storage\Collaborate;

use App\Enums\Storage\FileType;
use App\Models\AbstractModel;
use App\Models\Workflows\Pivots\TaskAttachment;
use App\Models\Workflows\Task;
use App\Traits\InteractsWithStorage;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\UploadedFile;

/**
 * @mixin IdeHelperAttachment
 */
class Attachment extends AbstractModel
{
    use InteractsWithStorage;

    protected $table = 'librarian_attachments';

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

    /**
     * @return BelongsToMany
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, (new TaskAttachment())->getTable())
            ->using(TaskAttachment::class);
    }
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
     * Get the mime type of the file.
     *
     * @return FileType
     */
    protected function getFileType(): FileType
    {
        return FileType::collaborators();
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
