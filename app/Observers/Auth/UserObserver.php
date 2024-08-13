<?php

namespace App\Observers\Auth;

use App\Models\Auth\User;
use App\Services\Auth\UserLifecycleService;
use App\Traits\NullToEmptyString;

class UserObserver
{
    use NullToEmptyString;

    /**
     * Get the list of fields that are not nullable.
     *
     * @return array<int,string>
     */
    protected function nonNullableFields(): array
    {
        return [
            'fname',
            'sname',
        ];
    }

    /**
     * Handle the User "saving" event.
     *
     * @return void
     */
    public function saving(User $user)
    {
        $this->nullToEmpty($user);
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function forceDeleted(User $user)
    {
        $user->deleteProfilePhoto();
    }

    /**
     * Listen to the User updated event.
     *
     * @param User $user
     *
     * @return void
     */
    public function updated(User $user)
    {
        if ($user->isDirty('password') && $user->hasBeenInvited()) {
            $lifecycleService = app(UserLifecycleService::class);
            $lifecycleService->activate($user);
        }
    }
}
