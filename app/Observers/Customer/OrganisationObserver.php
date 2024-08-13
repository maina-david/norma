<?php

namespace App\Observers\Customer;

use App\Jobs\Auth\CRMSyncOrganisationUsers;
use App\Jobs\Customer\SoftDeleteOrganisation;
use App\Models\Customer\Organisation;

class OrganisationObserver
{
    /**
     * Listen to the saving event.
     *
     * @param \App\Models\Customer\Organisation $organisation
     *
     * @return void
     */
    public function saving(Organisation $organisation): void
    {
        $organisation->setSlug();
    }

    /**
     * Listen to the Organisation deleted event.
     *
     * @param Organisation $organisation
     *
     * @return void
     */
    public function deleted(Organisation $organisation): void
    {
        SoftDeleteOrganisation::dispatch($organisation);
    }

    /**
     * Listen to the Organisation updated event.
     *
     * @param Organisation $organisation
     *
     * @return void
     */
    public function updated(Organisation $organisation): void
    {
        if ($organisation->isDirty(['title'])) {
            CRMSyncOrganisationUsers::dispatch($organisation);
        }

        // TODO: add this back in
        // app(OrganisationRepository::class)->applyMissingDefaultSettings($organisation);
    }

    /*
     * Checks if the update is only for the organisation's title
     */
    // protected function isTitleUpdate(array $changed): bool
    // {
    //     return array_key_exists('title', $changed);
    // }
}
