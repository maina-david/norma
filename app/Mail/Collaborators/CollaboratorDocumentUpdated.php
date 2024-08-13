<?php

namespace App\Mail\Collaborators;

use App\Models\Collaborators\ProfileDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollaboratorDocumentUpdated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param int $documentId
     */
    public function __construct(protected int $documentId)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /** @var ProfileDocument $document */
        $document = ProfileDocument::with(['profile.collaborator'])->findOrFail($this->documentId);

        /** @var string */
        $subject = __('collaborators.document_updated');

        return $this->subject($subject)
            ->markdown('emails.collaborators.profile-document-updated', [
                'document' => $document,
            ]);
    }
}
