<?php

namespace App\Services\Customer;

use App\Enums\Customer\OrganisationSwitcherMode;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ActiveOrganisationManager
{
    public const SESSION_ALL_ACTIVE = 'all';

    /**
     * @param int $organisationId
     *
     * @return void
     */
    public function activate(int $organisationId): void
    {
        /** @var User */
        $user = Auth::user();
        /** @var Organisation */
        $organisation = Organisation::findOrFail($organisationId);
        if (!$user->isInOrganisation($organisation) && !$organisation->userHasAccess($user)->exists()) {
            // @codeCoverageIgnoreStart
            abort(403);
            // @codeCoverageIgnoreEnd
        }
        Session::put(config('session-keys.customer.active-organisation'), $organisationId);
        $this->setMode(OrganisationSwitcherMode::single());
    }

    /**
     * @return void
     */
    public function activateAll(): void
    {
        $this->setMode(OrganisationSwitcherMode::all());
        Session::remove(config('session-keys.customer.active-organisation'));
    }

    /**
     * Get the currently active organisation for the given user.
     *
     * @return Organisation|null
     */
    public function getActive(): ?Organisation
    {
        $activeId = Session::get(config('session-keys.customer.active-organisation'));
        if ($activeId && $activeId !== static::SESSION_ALL_ACTIVE) {
            $org = Organisation::findOrFail($activeId);
            $activeOrg = $org instanceof Organisation ? $org : null;

            return $activeOrg;
        }

        return null;
    }

    /**
     * Get the currently organisation switcher mode, single or all.
     *
     * @return OrganisationSwitcherMode
     */
    public function getMode(): OrganisationSwitcherMode
    {
        $mode = Session::get(config('session-keys.customer.organisation-mode'), OrganisationSwitcherMode::single()->value);

        return OrganisationSwitcherMode::{$mode}();
    }

    /**
     * Sets the mode in the session to the given mode.
     *
     * @return void
     */
    public function setMode(OrganisationSwitcherMode $mode): void
    {
        Session::put(config('session-keys.customer.organisation-mode'), $mode->value);
    }

    /**
     * @param User $user
     *
     * @return Organisation|null
     */
    public function activateFirstAvailable(User $user): ?Organisation
    {
        if ($organisation = $user->organisations()->wherePivot('is_admin', true)->first()) {
            $this->activate($organisation->id);
        }

        return $organisation;
    }

    /**
     * Returns whether the currently active mode is single org mode.
     *
     * @return bool
     */
    public function isSingleOrgMode(): bool
    {
        return $this->getMode()->value === OrganisationSwitcherMode::single()->value;
    }
}
