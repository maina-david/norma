<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserActivity\ActivatedNorma;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Customer\Norma;
use App\Repositories\Auth\UserActivityRepository;
use App\Services\Customer\ActiveNormasManager;

class UserActivitySubscriber
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        protected UserActivityRepository $activityRepo
    ) {
    }

    /**
     * Handle the event.
     *
     * @param UserActivityEvent $event
     *
     * @return void
     */
    public function handle($event): void
    {
        if ($event instanceof ActivatedNorma) {
            $this->onActivatedNorma($event);
        } else {
            $this->onUserActivity($event);
        }
    }

    /**
     * @param UserActivityEvent $event
     **/
    public function onUserActivity(UserActivityEvent $event): void
    {
        $this->activityRepo->addUserActivityEvent($event);
    }

    /**
     * @param UserActivityEvent $event
     **/
    public function onActivatedNorma(UserActivityEvent $event): void
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Norma|null */
        $norma = $manager->get($event->getUser(), 1)->first();

        // don't add a place activated event if the last active place was the same place
        if (!$norma || $norma->id !== $event->getNorma()?->id) {
            $this->activityRepo->addUserActivityEvent($event);
        }
    }
}
