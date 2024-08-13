<?php

namespace App\Observers\Notify;

use App\Models\Notify\NotificationHandover;

class NotificationHandoverObserver
{
    /**
     * Listen to the saving event.
     *
     * @param \App\Models\Notify\NotificationHandover $handover
     *
     * @return void
     */
    public function saving(NotificationHandover $handover): void
    {
        $handover->include_meta = $handover->include_meta ?? [];
    }
}
