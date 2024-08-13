<?php

namespace App\Traits;

use App\Managers\ThemeManager;
use App\Models\Storage\My\Attachment;
use App\Stores\Storage\FileSystemStore;
use Hashids\Hashids;
use Illuminate\Http\UploadedFile;

trait HasProfilePhoto
{
    /**
     * Get the name to be used when creating the profile photo.
     *
     * @return string
     */
    abstract protected function getProfileName(): string;

    /**
     * Update the user's profile photo.
     *
     * @param \Illuminate\Http\UploadedFile $photo
     *
     * @return Attachment
     */
    public function updateProfilePhoto(UploadedFile $photo): Attachment
    {
        $attachment = (new Attachment())->saveFile($photo);
        if ($this->avatarAttachment) {
            app(FileSystemStore::class)->destroyFileInStorage($this->avatarAttachment->path, $this->avatarAttachment->mime_type);
            $this->avatarAttachment->delete();
        }
        $this->update(['avatar_attachment_id' => $attachment->id]);

        return $attachment;
    }

    /**
     * Delete the user's profile photo.
     *
     * @return void
     */
    public function deleteProfilePhoto()
    {
        if ($this->avatarAttachment) {
            app(FileSystemStore::class)->destroyFileInStorage($this->avatarAttachment->path, $this->avatarAttachment->mime_type);

            $this->forceFill([
                'avatar_attachment_id' => null,
            ])->save();
        }
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if (!$this->avatar_attachment_id) {
            return $this->defaultProfilePhotoUrl();
        }
        $hashIds = new Hashids(config('encrypt.avatar_url.passphrase'));
        $id = $hashIds->encode($this->avatar_attachment_id);

        return route('my.user.avatar.download', ['attachment' => $id]);
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        $theme = str_replace('#', '', app(ThemeManager::class)->current()->cssVariables()['primary'] ?? '#0A2B14');

        return sprintf(
            "https://ui-avatars.com/api/?name=%s&color=ffffff&background={$theme}",
            urlencode($this->getProfileName())
        );
    }
}
