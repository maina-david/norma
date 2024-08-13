<?php

namespace Tests\Unit\Services\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Mail\Auth\Onboarding\GettingStartedEmail;
use App\Mail\Auth\Onboarding\IntroEmail;
use App\Mail\Auth\Onboarding\InvitationEmail;
use App\Mail\Auth\Onboarding\PendingDeactivationEmail;
use App\Mail\Auth\Onboarding\UserGuideEmail;
use App\Models\Auth\User;
use App\Models\Log\UserLifecycleActivity;
use App\Services\Auth\UserLifecycleService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserLifecycleServiceTest extends TestCase
{
    public function testInvite(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'lifecycle_stage' => LifecycleStage::invitationSent()->value,
        ]);

        app(UserLifecycleService::class)->invite($user);
        Mail::assertNothingSent();

        $user = User::factory()->create([
            'lifecycle_stage' => LifecycleStage::notOnboarded()->value,
        ]);

        $countBefore = UserLifecycleActivity::count();
        app(UserLifecycleService::class)->invite($user);
        $countAfter = UserLifecycleActivity::count();

        Mail::assertSent(InvitationEmail::class);

        $this->assertTrue($countBefore < $countAfter);
        $this->assertTrue($user->lifecycle_stage === LifecycleStage::invitationSent()->value);
    }

    public function testReInvite(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'lifecycle_stage' => LifecycleStage::invitationSent()->value,
        ]);

        $countBefore = UserLifecycleActivity::count();
        app(UserLifecycleService::class)->reInvite($user);
        $countAfter = UserLifecycleActivity::count();
        Mail::assertSent(InvitationEmail::class);

        $this->assertTrue($countBefore < $countAfter);
        $this->assertTrue($user->lifecycle_stage === LifecycleStage::invitationSent()->value);
    }

    public function testHandleInvitationDeclined(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'lifecycle_stage' => LifecycleStage::invitationSent()->value,
        ]);

        $countBefore = UserLifecycleActivity::count();
        app(UserLifecycleService::class)->handleInvitationDeclined($user);
        $countAfter = UserLifecycleActivity::count();

        $user = $user->fresh();
        $this->assertFalse($user->active);
        $this->assertTrue($countBefore < $countAfter);
        $this->assertTrue($user->lifecycle_stage === LifecycleStage::invitationDeclined()->value);
    }

    public function testDeactivate(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'lifecycle_stage' => LifecycleStage::invitationSent()->value,
        ]);

        $countBefore = UserLifecycleActivity::count();
        app(UserLifecycleService::class)->deactivate($user);
        $countAfter = UserLifecycleActivity::count();

        $user = $user->fresh();
        $this->assertFalse($user->active);
        $this->assertTrue($countBefore < $countAfter);
        $this->assertTrue($user->lifecycle_stage === LifecycleStage::deactivated()->value);
    }

    public function testDeactivateDueToInactivity(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'lifecycle_stage' => LifecycleStage::invitationSent()->value,
        ]);

        $countBefore = UserLifecycleActivity::count();
        app(UserLifecycleService::class)->deactivateDueToInactivity($user);
        $countAfter = UserLifecycleActivity::count();

        $user = $user->fresh();
        $this->assertFalse($user->active);
        $this->assertTrue($countBefore < $countAfter);
        $this->assertTrue($user->lifecycle_stage === LifecycleStage::deactivatedInactivity()->value);
    }

    public function testActivate(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'lifecycle_stage' => LifecycleStage::deactivated()->value,
        ]);

        $countBefore = UserLifecycleActivity::count();
        app(UserLifecycleService::class)->activate($user);
        $countAfter = UserLifecycleActivity::count();

        $user = $user->fresh();
        $this->assertTrue($user->active);
        $this->assertTrue($countBefore < $countAfter);
        $this->assertTrue($user->lifecycle_stage === LifecycleStage::active()->value);
        Mail::assertSent(InvitationEmail::class);
    }

    public function testNotifyPendingDeactivation(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        app(UserLifecycleService::class)->notifyPendingDeactivation($user);

        Mail::assertQueued(PendingDeactivationEmail::class);
    }

    public function testSendIntroEmail(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        app(UserLifecycleService::class)->sendIntroEmail($user);

        Mail::assertQueued(IntroEmail::class);
    }

    public function testSendUserGuideEmail(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        app(UserLifecycleService::class)->sendUserGuideEmail($user);

        Mail::assertQueued(UserGuideEmail::class);
    }

    public function testSendGettingStartedEmail(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        app(UserLifecycleService::class)->sendGettingStartedEmail($user);

        Mail::assertQueued(GettingStartedEmail::class);
    }
}
