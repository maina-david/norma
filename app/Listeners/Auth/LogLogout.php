<?php

namespace App\Listeners\Auth;

use App\Models\System\Audit;
use Illuminate\Auth\Events\Logout;

class LogLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        /** @var \App\Models\Auth\User|null $user */
        $user = $event->user;

        if ($user) {
            Audit::custom($user, 'logout', ['email' => $user->email ?? '']);
        }
    }
}
