<?php

namespace App\Services\Customer;

use App\Actions\Auth\User\SetActiveLibryoAfterLogin;
use App\Enums\Customer\LibryoSwitcherMode;
use App\Events\Auth\UserActivity\ActivatedLibryo;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ActiveLibryosManager
{
    public const SESSION_ALL_ACTIVE = 'all';

    protected ?Organisation $activeOrganisation = null;

    protected ?Libryo $activeCache = null;

    protected ?LibryoSwitcherMode $modeCache = null;

    public function __construct(protected ActiveOrganisationManager $activeOrganisationManager)
    {
    }

    /**
     * Get the last active places limited to the given amount.
     *
     * @param User $user
     * @param int  $limit
     *
     * @return Collection<Libryo>
     **/
    public function get(
        User $user,
        int $limit
    ): Collection {
        // get last libryo activation activities
        $cursor = $user->activities()
            ->typeLibryoActivate()
            ->whereHas('libryo', fn ($q) => $q->userHasAccess($user)->whereNotNull('organisation_id')->active())
            ->with([
                'libryo' => fn ($q) => $q->active(),
            ])
            ->cursor();
        $uniqueIds = [];
        /** @var Collection<Libryo> */
        $libryos = (new Libryo())->newCollection();
        foreach ($cursor as $activity) {
            if ($libryos->count() === $limit) {
                break;
            }
            if (isset($uniqueIds[$activity->place_id])) {
                continue;
            }
            /** @var Libryo $lib */
            $lib = $activity->libryo;
            $libryos->add($lib);
            $uniqueIds[$lib->id] = true;
        }

        return $libryos;
    }

    /**
     * @param User   $user
     * @param Libryo $libryo
     *
     * @return void
     */
    public function activate(User $user, Libryo $libryo): void
    {
        Session::put(config('session-keys.customer.active-libryo'), $libryo->id);
        ActivatedLibryo::dispatch($user, $libryo);
        $this->setMode(LibryoSwitcherMode::single());
        $this->activeCache = $libryo;
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

        $this->setMode(LibryoSwitcherMode::all());
        $libryo = $this->getActive($user);

        if ($libryo) {
            $organisation = $libryo->organisation;
        } else {
            $organisation = $user->organisations()->first();
        }

        if (is_null($organisation)) {
            // @codeCoverageIgnoreStart
            abort(403);
            // @codeCoverageIgnoreEnd
        }
        $this->activeOrganisationManager->activate($organisation->id);

        Session::remove(config('session-keys.customer.active-libryo'));

        $this->flushCache();
    }

    /**
     * Get the currently active libryo stream for the given user.
     * If none available in the session.
     *
     * @param User|null $user
     *
     * @return Libryo|null
     */
    public function getActive(?User $user = null): ?Libryo
    {
        if (!is_null($this->activeCache)) {
            return $this->activeCache;
        }
        /** @var User $user */
        $user = $user ?? Auth::user();
        $activeId = Session::get(config('session-keys.customer.active-libryo'));
        if ($activeId) {
            /** @var Libryo $libryo */
            $libryo = Libryo::findOrFail($activeId);
            $this->activeCache = $libryo;

            return $libryo;
        }

        // from production bugs
        // if for some reason we still don't have an active user try run set active again
        if ($this->isSingleMode()) {
            SetActiveLibryoAfterLogin::run($user);

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
     * @return Libryo|null
     */
    // public function activateFirstAvailable(User $user): Libryo|null
    // {
    //     if (!$libryo = $this->get($user, 1)->first()) {
    //         $libryo = Libryo::userHasAccess($user)->first();
    //     }

    //     if ($libryo) {
    //         $this->activate($user, $libryo);
    //     }

    //     return $libryo;
    // }

    /**
     * Sets the mode in the session to the given mode.
     *
     * @return void
     */
    public function setMode(LibryoSwitcherMode $mode): void
    {
        Session::put(config('session-keys.customer.active-libryo-mode'), $mode->value);
        $this->modeCache = null;
    }

    /**
     * Get the currently organisation switcher mode, single or all.
     *
     * @return LibryoSwitcherMode
     */
    public function getMode(): LibryoSwitcherMode
    {
        if (!is_null($this->modeCache)) {
            return $this->modeCache;
        }
        $mode = Session::get(config('session-keys.customer.active-libryo-mode'), LibryoSwitcherMode::single()->value);

        /** @var LibryoSwitcherMode */
        $mode = LibryoSwitcherMode::fromValue($mode);

        /** @var LibryoSwitcherMode */
        return $this->modeCache = $mode;
    }

    /**
     * Returns whether the currently active mode is single stream mode.
     *
     * @return bool
     */
    public function isSingleMode(): bool
    {
        return $this->getMode()->value === LibryoSwitcherMode::single()->value;
    }

    /**
     * Get the currently active organisation by libryo switcher mode:
     * when in single libryo mode, then it's the currently active libryo's org
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
            /** @var Libryo|null */
            $libryo = $this->getActive();
            if (is_null($libryo)) {
                // @codeCoverageIgnoreStart
                /** @var User $user */
                $user = Auth::user();
                /** @var Collection<Libryo> */
                $lastActiveLibryos = $this->get($user, 1);
                /** @var Libryo */
                $libryo = $lastActiveLibryos->first();
                // @codeCoverageIgnoreEnd
            }

            $this->activeOrganisation = $libryo->organisation;

            /** @var Organisation */
            return $libryo->organisation;
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
