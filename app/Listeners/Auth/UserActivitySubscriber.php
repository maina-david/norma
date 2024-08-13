<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserActivity\ActivatedLibryo;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Customer\Libryo;
use App\Repositories\Auth\UserActivityRepository;
use App\Services\Customer\ActiveLibryosManager;

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
        if ($event instanceof ActivatedLibryo) {
            $this->onActivatedLibryo($event);
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
    public function onActivatedLibryo(UserActivityEvent $event): void
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var Libryo|null */
        $libryo = $manager->get($event->getUser(), 1)->first();

        // don't add a place activated event if the last active place was the same place
        if (!$libryo || $libryo->id !== $event->getLibryo()?->id) {
            $this->activityRepo->addUserActivityEvent($event);
        }
    }
}
