<?php

namespace App\Actions\Auth\User;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Authenticatable;
use Lorisleiva\Actions\Concerns\AsAction;

class SetActiveLibryoAfterLogin
{
    use AsAction;

    public function handle(Authenticatable $user): void
    {
        /** @var User $user */
        $manager = app(ActiveLibryosManager::class);
        $lastActiveLibryos = $manager->get($user, 1);

        /** @var Libryo|null */
        $libryo = Libryo::userHasAccess($user)
            ->whereKey($lastActiveLibryos->modelKeys())
            ->first();

        if (!is_null($libryo)) {
            $manager->activate($user, $libryo);
        } else {
            // user hasn't activated a libryo before, so use first available
            /** @var Libryo|null */
            $libryo = Libryo::active()->whereNotNull('organisation_id')->userHasAccess($user)->first();
            if (is_null($libryo)) {
                // @codeCoverageIgnoreStart
                return;
                // @codeCoverageIgnoreEnd
            }
            $manager->activate($user, $libryo);
        }
    }

    public function asListener(Login $event): void
    {
        $this->handle($event->user);
    }
}
