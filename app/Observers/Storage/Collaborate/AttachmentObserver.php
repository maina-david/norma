<?php

namespace App\Observers\Storage\Collaborate;

use App\Models\Storage\Collaborate\Attachment;
use Illuminate\Support\Facades\Auth;

class AttachmentObserver
{
    /**
     * Listen to the creating event.
     *
     * @param Attachment $attachment
     *
     * @return void
     */
    public function creating(Attachment $attachment): void
    {
        /** @var int|null $userId */
        $userId = Auth::id();

        $attachment->author_id = $userId;
    }

    /**
     * Listen to the deleted event.
     *
     * @param Attachment $attachment
     *
     * @return void
     */
    public function deleted(Attachment $attachment): void
    {
        $attachment->deleteFile();
    }
}
