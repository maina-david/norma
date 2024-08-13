<?php

namespace App\Observers\Collaborators;

use App\Enums\Collaborators\DocumentType;
use App\Mail\Collaborators\CollaboratorDocumentUpdated;
use App\Models\Collaborators\Profile;
use App\Models\Collaborators\ProfileDocument;
use App\Models\Storage\Collaborate\Attachment;
use Illuminate\Support\Facades\Mail;

class ProfileDocumentObserver
{
    /**
     * Listen to the created event.
     *
     * @param ProfileDocument $document
     *
     * @return void
     */
    public function created(ProfileDocument $document): void
    {
        ProfileDocument::where('id', '!=', $document->id)
            ->where('profile_id', $document->profile_id)
            ->where('type', $document->type)
            ->where('active', false)
            ->whereNull('approved_at')
            ->get()
            ->each(fn (ProfileDocument $item) => $item->delete());

        $notify = [
            DocumentType::IDENTIFICATION,
            DocumentType::TRANSCRIPT,
            DocumentType::VISA,
            DocumentType::VITAE,
        ];

        $forCollaborator = Profile::whereKey($document->profile_id)->whereNotNull('user_id')->exists();

        if ($forCollaborator && in_array($document->type, $notify)) {
            Mail::bcc(config('collaborate.emails.document_validation'))
                ->send(new CollaboratorDocumentUpdated($document->id));
        }
    }

    /**
     * Listen to the saved event.
     *
     * @param ProfileDocument $document
     *
     * @return void
     */
    public function saved(ProfileDocument $document): void
    {
        if ($document->active && $document->isDirty(['active'])) {
            ProfileDocument::where('id', '!=', $document->id)
                ->where('profile_id', $document->profile_id)
                ->where('type', $document->type)
                ->update(['active' => false, 'expired' => true]);
        }
    }

    /**
     * Listen to the deleted event.
     *
     * @param ProfileDocument $document
     *
     * @return void
     */
    public function deleted(ProfileDocument $document): void
    {
        $attachment = Attachment::find($document->attachment_id);
        $attachment?->delete();
    }
}
