<?php

namespace App\Listeners\Auth;

use App\Models\System\Audit;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Login $event
     */
    public function handle(Login $event): void
    {
        /** @var \App\Models\Auth\User $user */
        $user = $event->user;

        Audit::custom($user, 'login:success', ['email' => $user->email]);
    }
}
