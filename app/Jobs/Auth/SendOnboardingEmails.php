<?php

namespace App\Jobs\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Models\Auth\User;
use App\Services\Auth\UserLifecycleService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SendOnboardingEmails implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(UserLifecycleService $service)
    {
        $this->sendAdditionalInvitations($service);
        $this->sendIntroEmails($service);
        $this->sendUserGuideEmails($service);
        $this->sendGettingStartedEmails($service);
        $this->sendPendingDeactivationEmails($service);
    }

    /**
     * Execute the given callable for each user.
     *
     * @param int      $days
     * @param callable $each
     *
     * @return void
     */
    private function executeCommonCustomerQuery(int $days, callable $each): void
    {
        User::notIntegrationUser()
            ->where('user_type', UserType::customer()->value)
            ->whereBetween('created_at', [
                now()->startOfDay()->subDays($days),
                now()->startOfDay()->subDays($days - 1),
            ])
            ->chunk(200, function (Collection $users) use ($each) {
                $users->each(fn (User $user) => call_user_func($each, $user));
            });
    }

    /**
     * Send follow-up invitation to users who haven't logged in.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    private function sendAdditionalInvitations(UserLifecycleService $service): void
    {
        User::notIntegrationUser()
            ->where('lifecycle_stage', LifecycleStage::invitationSent()->value)
            ->where(function ($builder) {
                collect(config('libryo.user.lifecycle.resend_invitation_after_days', []))
                    ->each(function ($days, $index) use ($builder) {
                        $method = $index === 0 ? 'where' : 'orWhere';

                        $builder->{$method}(function ($query) use ($days) {
                            $query->whereBetween('created_at', [
                                now()->startOfDay()->subDays($days),
                                now()->startOfDay()->subDays($days - 1),
                            ]);
                        });
                    });
            })
            ->chunk(200, function (Collection $users) use ($service) {
                $users->each(function (User $user) use ($service) {
                    // Prevents invitation being sent more than once a day
                    $cacheKey = config('cache-keys.auth.user.user-invite-sent.prefix') . $user->id;

                    if (!Cache::has($cacheKey)) {
                        $service->sendInvitation($user);
                    }
                });
            });
    }

    /**
     * Send the intro emails 1 day after user creation.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    private function sendIntroEmails(UserLifecycleService $service): void
    {
        $this->executeCommonCustomerQuery(1, fn ($user) => $service->sendIntroEmail($user));
    }

    /**
     * Send the user guides 2 days after user creation.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    private function sendUserGuideEmails(UserLifecycleService $service): void
    {
        $this->executeCommonCustomerQuery(2, fn ($user) => $service->sendUserGuideEmail($user));
    }

    /**
     * Send the getting started emails 3 days after user creation.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    private function sendGettingStartedEmails(UserLifecycleService $service): void
    {
        $this->executeCommonCustomerQuery(3, fn ($user) => $service->sendGettingStartedEmail($user));
    }

    /**
     * Send the pending deactivation email after the configured months of inactivity.
     *
     * @param UserLifecycleService $service
     *
     * @return void
     */
    private function sendPendingDeactivationEmails(UserLifecycleService $service): void
    {
        User::notIntegrationUser()
            ->where('lifecycle_stage', LifecycleStage::active()->value)
            ->whereNotIn('user_type', [UserType::collaborate()->value, UserType::partner()->value])
            ->whereDoesntHave('activities', function ($builder) {
                $builder->where(
                    'created_at',
                    '>=',
                    Carbon::now()
                        ->startOfDay()
                        ->subMonths(config('libryo.user.lifecycle.notify_deactivation_when.inactivity_months_reach'))
                );
            })
            ->whereHas('activities', function ($builder) {
                $builder->where(
                    'created_at',
                    '>=',
                    Carbon::now()
                        ->startOfDay()
                        ->subMonths(config('libryo.user.lifecycle.deactivate_when.inactivity_months_reach'))
                );
            })
            ->chunk(200, function ($users) use ($service) {
                $users->each(function ($user) use ($service) {
                    $cacheKey = config('cache-keys.auth.user.user-pending-deactivation.prefix') . $user->id;

                    if (!Cache::has($cacheKey)) {
                        $service->notifyPendingDeactivation($user);
                    }
                });
            });
    }
}
