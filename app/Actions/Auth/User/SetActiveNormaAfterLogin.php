<?php

namespace App\Actions\Auth\User;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Auth\Authenticatable;
use Lorisleiva\Actions\Concerns\AsAction;

class SetActiveNormaAfterLogin
{
    use AsAction;

    public function handle(Authenticatable $user): void
    {
        /** @var User $user */
        $manager = app(ActiveNormasManager::class);
        $lastActiveNormas = $manager->get($user, 1);

        /** @var Norma|null */
        $norma = Norma::userHasAccess($user)
            ->whereKey($lastActiveNormas->modelKeys())
            ->first();

        if (!is_null($norma)) {
            $manager->activate($user, $norma);
        } else {
            // user hasn't activated a norma before, so use first available
            /** @var Norma|null */
            $norma = Norma::active()->whereNotNull('organisation_id')->userHasAccess($user)->first();
            if (is_null($norma)) {
                // @codeCoverageIgnoreStart
                return;
                // @codeCoverageIgnoreEnd
            }
            $manager->activate($user, $norma);
        }
    }

    public function asListener(Login $event): void
    {
        $this->handle($event->user);
    }
}
