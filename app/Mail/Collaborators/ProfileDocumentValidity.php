<?php

namespace App\Mail\Collaborators;

use App\Models\Collaborators\ProfileDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProfileDocumentValidity extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public ProfileDocument $document)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /** @var \Carbon\Carbon $validTo */
        $validTo = $this->document->valid_to;
        /** @var string $subject */
        $subject = now()->isSameDay($validTo)
            ? __('notifications.profile_document_expiring', ['document' => $this->document->type->label()])
            : __('notifications.profile_document_expired', ['document' => $this->document->type->label()]);

        $this->document->load(['profile.user']);

        return $this->subject($subject)
            ->markdown('emails.collaborators.profile-document-expired', ['document' => $this->document]);
    }
}
