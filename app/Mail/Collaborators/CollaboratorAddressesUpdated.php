<?php

namespace App\Mail\Collaborators;

use App\Models\Auth\User;
use App\Models\Collaborators\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CollaboratorAddressesUpdated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected Profile $profile)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        /** @var string $subject */
        $subject = __('collaborators.update_addresses.subject');

        $this->profile->load(['user']);

        /** @var User $user */
        $user = $this->profile->user;

        return $this->markdown('emails.collaborators.collaborator-addresses-updated')
            ->with(['user' => $user])
            ->subject($subject);
    }
}
