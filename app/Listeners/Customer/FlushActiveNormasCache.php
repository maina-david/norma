<?php

namespace App\Listeners\Customer;

use App\Services\Customer\ActiveNormasManager;

/**
 * @codeCoverageIgnore
 */
class FlushActiveNormasCache
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
        if (!$event->sandbox->resolved(ActiveNormasManager::class)) {
            return;
        }

        /** @var ActiveNormasManager */
        $manager = $event->sandbox->make(ActiveNormasManager::class);

        if (method_exists($manager, 'flushCache')) {
            $manager->flushCache();
        }
    }
}
