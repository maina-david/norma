<?php

namespace App\Observers\Collaborators;

use App\Mail\Collaborators\CollaboratorAddressesUpdated;
use App\Mail\Collaborators\CollaboratorUnsubscribedFromCommunication;
use App\Models\Collaborators\Profile;
use Illuminate\Support\Facades\Mail;

class ProfileObserver
{
    /**
     * @param Profile $profile
     *
     * @return void
     */
    public function updated(Profile $profile): void
    {
        if ($profile->user_id) {
            $this->notifyChanges($profile);
        }
    }

    /**
     * @param Profile $profile
     *
     * @return void
     */
    protected function notifyChanges(Profile $profile): void
    {
        if ($profile->isDirty('allows_communication') && !$profile->allows_communication) {
            Mail::to($profile->user)->send(new CollaboratorUnsubscribedFromCommunication($profile));
        }

        if ($profile->isDirty(['address_line_1', 'address_line_2', 'address_line_3', 'address_country_code', 'postal_address'])) {
            Mail::to(config('collaborate.get_in_touch_email'))->send(new CollaboratorAddressesUpdated($profile));
        }
    }
}
