<?php

namespace App\Jobs\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Models\Auth\User;
use App\Services\Auth\UserLifecycleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class UpdateLifecycles implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    public function handle(UserLifecycleService $service): void
    {
        $this->updateDeclinedInvitations($service);
        $this->deactivateUsers($service);
    }

    /**
     * Decline old invitations.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    protected function updateDeclinedInvitations(UserLifecycleService $service): void
    {
        $lastInviteDate = now()
            ->subDays(config('norma.user.lifecycle.deactivate_when.invitations_days_reach'))
            ->toDateString();

        User::where('user_type', '!=', UserType::collaborate()->value)
            ->where('lifecycle_stage', LifecycleStage::invitationSent()->value)
            ->where('settings->last_invite_sent', '<=', $lastInviteDate)
            ->chunk(200, function (Collection $users) use ($service) {
                $users->each(fn (User $user) => $service->handleInvitationDeclined($user));
            });
    }

    /**
     * Deactivate inactive users.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    protected function deactivateUsers(UserLifecycleService $service): void
    {
        $lastActivityDate = now()->subMonths(config('norma.user.lifecycle.deactivate_when.inactivity_months_reach'));

        User::where('lifecycle_stage', LifecycleStage::active()->value)
            ->whereNotIn('user_type', [UserType::collaborate()->value, UserType::partner()->value])
            ->where('created_at', '<=', $lastActivityDate)
            ->whereDoesntHave('activities', fn ($q) => $q->where('created_at', '>=', $lastActivityDate))
            ->notIntegrationUser()
            ->chunk(200, function (Collection $users) use ($service) {
                $users->each(fn (User $user) => $service->deactivateDueToInactivity($user));
            });
    }
}
