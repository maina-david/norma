<?php

namespace Tests\Feature\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Mail\Auth\Onboarding\GettingStartedEmail;
use App\Mail\Auth\Onboarding\IntroEmail;
use App\Mail\Auth\Onboarding\InvitationEmail;
use App\Mail\Auth\Onboarding\PendingDeactivationEmail;
use App\Mail\Auth\Onboarding\UserGuideEmail;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendOnboardingEmailsTest extends TestCase
{
    /**
     * Create a user in the past.
     *
     * @param int   $days
     * @param array $attributes
     *
     * @return User
     */
    protected function createOldUser(int $days, array $attributes = []): User
    {
        $this->travelTo(now()->subDays($days));

        $user = User::factory()->create([
            'user_type' => UserType::customer()->value,
            'lifecycle_stage' => LifecycleStage::invitationSent()->value,
            ...$attributes,
        ]);

        $this->travelBack();

        return $user;
    }

    /**
     * Assert that the mail was sent/queued and a cache key, if present, has been set.
     *
     * @param User        $user
     * @param string      $mailClass
     * @param string|null $cacheKey
     * @param bool        $queued
     *
     * @return void
     */
    protected function assertMailSentAndCached(User $user, string $mailClass, ?string $cacheKey = null, bool $queued = false): void
    {
        Mail::fake();

        Mail::assertNotOutgoing($mailClass, function ($mail) use ($user) {
            return $mail->user->id === $user->id;
        });

        Artisan::call('norma:review-lifecycles');

        if ($cacheKey) {
            $this->assertTrue(Cache::has($cacheKey));
        }

        $method = $queued ? 'assertQueued' : 'assertSent';

        Mail::{$method}($mailClass, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /**
     * @return void
     */
    public function testItSendsAdditionalInvitations(): void
    {
        collect(config('norma.user.lifecycle.resend_invitation_after_days', []))
            ->each(function ($days) {
                $user = $this->createOldUser($days);

                $cacheKey = config('cache-keys.auth.user.user-invite-sent.prefix') . $user->id;

                $this->assertMailSentAndCached($user, InvitationEmail::class, $cacheKey);
            });
    }

    /**
     * @return void
     */
    public function testItSendsIntroEmails(): void
    {
        $user = $this->createOldUser(1);

        $this->assertMailSentAndCached($user, IntroEmail::class, null, true);
    }

    /**
     * @return void
     */
    public function testItSendsUserGuide(): void
    {
        $user = $this->createOldUser(2);

        $this->assertMailSentAndCached($user, UserGuideEmail::class, null, true);
    }

    /**
     * @return void
     */
    public function testItSendsGettingStartedEmails(): void
    {
        $user = $this->createOldUser(3);

        $this->assertMailSentAndCached($user, GettingStartedEmail::class, null, true);
    }

    /**
     * @return void
     */
    public function testItSendsPendingDeactivationEmails(): void
    {
        $user = $this->createOldUser(3, ['lifecycle_stage' => LifecycleStage::active()->value]);

        $this->travelTo(now()->subMonths(config('norma.user.lifecycle.deactivate_when.inactivity_months_reach')));

        UserActivity::factory()->create(['user_id' => $user->id]);

        $this->travelBack();

        $cacheKey = config('cache-keys.auth.user.user-pending-deactivation.prefix') . $user->id;

        $this->assertMailSentAndCached($user, PendingDeactivationEmail::class, $cacheKey, true);
    }
}
