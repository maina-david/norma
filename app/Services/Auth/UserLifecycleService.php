<?php

namespace App\Services\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserActivityType;
use App\Events\Log\UserLifecycleChange;
use App\Jobs\Auth\SendUserInviteEmail;
use App\Mail\Auth\Onboarding\GettingStartedEmail;
use App\Mail\Auth\Onboarding\IntroEmail;
use App\Mail\Auth\Onboarding\PendingDeactivationEmail;
use App\Mail\Auth\Onboarding\UserGuideEmail;
use App\Models\Auth\User;
use App\Repositories\Auth\UserActivityRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserLifecycleService
{
    /**
     * Invite the user to the platform.
     *
     * @param User $user
     *
     * @return void
     */
    public function invite(User $user): void
    {
        $from_stage = $user->lifecycle_stage;
        $to_stage = LifecycleStage::invitationSent()->value;

        if ($from_stage === $to_stage) {
            return;
        }

        $user->active = false;
        $user->lifecycle_stage = $to_stage;

        $user->updateSetting('last_invite_sent', Carbon::now()->toDateString());

        $this->sendInvitation($user);
        UserLifecycleChange::dispatch($user, $from_stage, $to_stage);
    }

    /**
     * @param User $user
     *
     * @return void
     */
    public function reInvite(User $user): void
    {
        $user->lifecycle_stage = LifecycleStage::notOnboarded()->value;
        $user->save();
        $this->invite($user);
    }

    /**
     * Handle the lifecycle changes needed for the user declining the invitation.
     *
     * @param User $user
     *
     * @return void
     */
    public function handleInvitationDeclined(User $user): void
    {
        $from_stage = $user->lifecycle_stage;
        $to_stage = LifecycleStage::invitationDeclined()->value;

        if ($from_stage === $to_stage) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $user->active = false;
        $user->lifecycle_stage = $to_stage;
        $user->save();

        UserLifecycleChange::dispatch($user, $from_stage, $to_stage);
    }

    /**
     * Deactivate the user.
     *
     * @param User $user
     *
     * @return void
     */
    public function deactivate(User $user): void
    {
        $from_stage = $user->lifecycle_stage;
        $to_stage = LifecycleStage::deactivated()->value;

        if ($from_stage === $to_stage) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $user->active = false;
        $user->lifecycle_stage = $to_stage;
        $user->save();

        DB::table('oauth_access_tokens')->where('user_id', $user->id)->delete();

        UserLifecycleChange::dispatch($user, $from_stage, $to_stage);
    }

    /**
     * Deactivate the user due to inactivity.
     *
     * @param User $user
     *
     * @return void
     */
    public function deactivateDueToInactivity(User $user): void
    {
        $from_stage = $user->lifecycle_stage;
        $to_stage = LifecycleStage::deactivatedInactivity()->value;

        if ($from_stage === $to_stage) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $user->active = false;
        $user->lifecycle_stage = $to_stage;
        $user->save();

        UserLifecycleChange::dispatch($user, $from_stage, $to_stage);
    }

    /**
     * Activate the user.
     *
     * @param User $user
     *
     * @return void
     */
    public function activate(User $user): void
    {
        $from_stage = $user->lifecycle_stage;
        $to_stage = LifecycleStage::active()->value;

        if ($from_stage === $to_stage) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        if (in_array($user->lifecycle_stage, config('libryo.user.lifecycle.deactivated_stages'))) {
            $this->sendInvitation($user);
        }

        $user->active = true;
        $user->lifecycle_stage = $to_stage;
        $user->save();

        app(UserActivityRepository::class)->addActivity($user, UserActivityType::userActivated(), false);

        UserLifecycleChange::dispatch($user, $from_stage, $to_stage);
    }

    /**
     * Send the user an invitation email.
     *
     * @param User $user
     *
     * @return void
     */
    public function sendInvitation(User $user): void
    {
        $cacheKey = config('cache-keys.auth.user.user-invite-sent.prefix') . $user->id;
        Cache::put($cacheKey, Carbon::now(), config('cache-keys.auth.user.user-invite-sent.expiry'));

        if ($user->email) {
            SendUserInviteEmail::dispatch($user);
        }
    }

    /**
     * Notify the user that they have a pending deactivation.
     *
     * @param User $user
     *
     * @return void
     */
    public function notifyPendingDeactivation(User $user): void
    {
        $cacheKey = config('cache-keys.auth.user.user-pending-deactivation.prefix') . $user->id;
        Cache::put($cacheKey, Carbon::now(), config('cache-keys.auth.user.user-pending-deactivation.expiry'));

        Mail::to($user->email)
            ->queue(new PendingDeactivationEmail($user));
    }

    /**
     * Send the user the intro to libryo email.
     *
     * @param User $user
     *
     * @return void
     */
    public function sendIntroEmail(User $user): void
    {
        Mail::to($user->email)
            ->queue(new IntroEmail($user));
    }

    /**
     * Send the user the user guide email.
     *
     * @param User $user
     *
     * @return void
     */
    public function sendUserGuideEmail(User $user): void
    {
        Mail::to($user->email)
            ->queue(new UserGuideEmail($user));
    }

    /**
     * Send the user the getting started email.
     *
     * @param User $user
     *
     * @return void
     */
    public function sendGettingStartedEmail(User $user): void
    {
        Mail::to($user->email)
            ->queue(new GettingStartedEmail($user));
    }
}
