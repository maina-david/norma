<?php

namespace App\Listeners\Auth;

use App\Models\Auth\User;
use App\Models\System\Audit;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Failed $event
     */
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? '';

        /** @var \App\Models\Auth\User|null $user */
        $user = $event->user ?? User::where('email', $email)->first();

        if ($user) {
            Audit::custom($user, 'login:failed', ['email' => $email]);
        }
    }
}
