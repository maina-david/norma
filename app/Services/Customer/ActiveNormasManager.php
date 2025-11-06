<?php

namespace App\Services\Customer;

use App\Actions\Auth\User\SetActiveNormaAfterLogin;
use App\Enums\Customer\NormaSwitcherMode;
use App\Events\Auth\UserActivity\ActivatedNorma;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ActiveNormasManager
{
    public const SESSION_ALL_ACTIVE = 'all';

    protected ?Organisation $activeOrganisation = null;

    protected ?Norma $activeCache = null;

    protected ?NormaSwitcherMode $modeCache = null;

    public function __construct(protected ActiveOrganisationManager $activeOrganisationManager)
    {
    }

    /**
     * Get the last active places limited to the given amount.
     *
     * @param User $user
     * @param int  $limit
     *
     * @return Collection<Norma>
     **/
    public function get(
        User $user,
        int $limit
    ): Collection {
        // get last norma activation activities
        $cursor = $user->activities()
            ->typeNormaActivate()
            ->whereHas('norma', fn ($q) => $q->userHasAccess($user)->whereNotNull('organisation_id')->active())
            ->with([
                'norma' => fn ($q) => $q->active(),
            ])
            ->cursor();
        $uniqueIds = [];
        /** @var Collection<Norma> */
        $normas = (new Norma())->newCollection();
        foreach ($cursor as $activity) {
            if ($normas->count() === $limit) {
                break;
            }
            if (isset($uniqueIds[$activity->place_id])) {
                continue;
            }
            /** @var Norma $lib */
            $lib = $activity->norma;
            $normas->add($lib);
            $uniqueIds[$lib->id] = true;
        }

        return $normas;
    }

    /**
     * @param User   $user
     * @param Norma $norma
     *
     * @return void
     */
    public function activate(User $user, Norma $norma): void
    {
        Session::put(config('session-keys.customer.active-norma'), $norma->id);
        ActivatedNorma::dispatch($user, $norma);
        $this->setMode(NormaSwitcherMode::single());
        $this->activeCache = $norma;
        $this->activeOrganisation = null;
    }

    /**
     * @param User|null|null $user
     *
     * @return void
     */
    public function activateAll(?User $user = null): void
    {
        /** @var User $user */
        $user = $user ?? Auth::user();

        $this->setMode(NormaSwitcherMode::all());
        $norma = $this->getActive($user);

        if ($norma) {
            $organisation = $norma->organisation;
        } else {
            $organisation = $user->organisations()->first();
        }

        if (is_null($organisation)) {
            // @codeCoverageIgnoreStart
            abort(403);
            // @codeCoverageIgnoreEnd
        }
        $this->activeOrganisationManager->activate($organisation->id);

        Session::remove(config('session-keys.customer.active-norma'));

        $this->flushCache();
    }

    /**
     * Get the currently active norma stream for the given user.
     * If none available in the session.
     *
     * @param User|null $user
     *
     * @return Norma|null
     */
    public function getActive(?User $user = null): ?Norma
    {
        if (!is_null($this->activeCache)) {
            return $this->activeCache;
        }
        /** @var User $user */
        $user = $user ?? Auth::user();
        $activeId = Session::get(config('session-keys.customer.active-norma'));
        if ($activeId) {
            /** @var Norma $norma */
            $norma = Norma::findOrFail($activeId);
            $this->activeCache = $norma;

            return $norma;
        }

        // from production bugs
        // if for some reason we still don't have an active user try run set active again
        if ($this->isSingleMode()) {
            SetActiveNormaAfterLogin::run($user);

            return $this->activeCache;
        }

        return null;
        // return $this->activateFirstAvailable($user);
    }

    /**
     * Commented as it's not being used yet.
     *
     * @param User $user
     *
     * @return Norma|null
     */
    // public function activateFirstAvailable(User $user): Norma|null
    // {
    //     if (!$norma = $this->get($user, 1)->first()) {
    //         $norma = Norma::userHasAccess($user)->first();
    //     }

    //     if ($norma) {
    //         $this->activate($user, $norma);
    //     }

    //     return $norma;
    // }

    /**
     * Sets the mode in the session to the given mode.
     *
     * @return void
     */
    public function setMode(NormaSwitcherMode $mode): void
    {
        Session::put(config('session-keys.customer.active-norma-mode'), $mode->value);
        $this->modeCache = null;
    }

    /**
     * Get the currently organisation switcher mode, single or all.
     *
     * @return NormaSwitcherMode
     */
    public function getMode(): NormaSwitcherMode
    {
        if (!is_null($this->modeCache)) {
            return $this->modeCache;
        }
        $mode = Session::get(config('session-keys.customer.active-norma-mode'), NormaSwitcherMode::single()->value);

        /** @var NormaSwitcherMode */
        $mode = NormaSwitcherMode::fromValue($mode);

        /** @var NormaSwitcherMode */
        return $this->modeCache = $mode;
    }

    /**
     * Returns whether the currently active mode is single stream mode.
     *
     * @return bool
     */
    public function isSingleMode(): bool
    {
        return $this->getMode()->value === NormaSwitcherMode::single()->value;
    }

    /**
     * Get the currently active organisation by norma switcher mode:
     * when in single norma mode, then it's the currently active norma's org
     * When in all streams mode, then get the active org from the session.
     *
     * @return Organisation
     */
    public function getActiveOrganisation(): Organisation
    {
        if ($this->activeOrganisation) {
            // @codeCoverageIgnoreStart
            return $this->activeOrganisation;
            // @codeCoverageIgnoreEnd
        }

        if ($this->isSingleMode()) {
            /** @var Norma|null */
            $norma = $this->getActive();
            if (is_null($norma)) {
                // @codeCoverageIgnoreStart
                /** @var User $user */
                $user = Auth::user();
                /** @var Collection<Norma> */
                $lastActiveNormas = $this->get($user, 1);
                /** @var Norma */
                $norma = $lastActiveNormas->first();
                // @codeCoverageIgnoreEnd
            }

            $this->activeOrganisation = $norma->organisation;

            /** @var Organisation */
            return $norma->organisation;
        }

        $active = $this->activeOrganisation = $this->activeOrganisationManager->getActive();

        if (is_null($active)) {
            $this->activateAll();
            $active = $this->activeOrganisation = $this->activeOrganisationManager->getActive();
        }

        /** @var Organisation */
        return $active;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function flushCache(): void
    {
        $this->activeOrganisation = null;
        $this->activeCache = null;
        $this->modeCache = null;
    }
}
