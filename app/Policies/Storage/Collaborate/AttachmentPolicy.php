<?php

namespace App\Policies\Storage\Collaborate;

use App\Models\Auth\User;
use App\Models\Storage\Collaborate\Attachment;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param User       $user
     * @param Attachment $attachment
     *
     * @return bool
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->can('collaborate.storage.attachment.delete')
            || $attachment->author_id === $user->id;
    }
}
