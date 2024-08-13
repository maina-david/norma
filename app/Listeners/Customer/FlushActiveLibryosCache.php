<?php

namespace App\Listeners\Customer;

use App\Services\Customer\ActiveLibryosManager;

/**
 * @codeCoverageIgnore
 */
class FlushActiveLibryosCache
{
    /**
     * Handle the event.
     *
     * @param mixed $event
     *
     * @return void
     */
    public function handle($event)
    {
        if (!$event->sandbox->resolved(ActiveLibryosManager::class)) {
            return;
        }

        /** @var ActiveLibryosManager */
        $manager = $event->sandbox->make(ActiveLibryosManager::class);

        if (method_exists($manager, 'flushCache')) {
            $manager->flushCache();
        }
    }
}
