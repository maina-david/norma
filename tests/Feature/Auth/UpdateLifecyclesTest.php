<?php

namespace Tests\Feature\Auth;

use App\Enums\Auth\LifecycleStage;
use App\Enums\Auth\UserType;
use App\Models\Auth\User;
use Artisan;
use Tests\TestCase;

class UpdateLifecyclesTest extends TestCase
{
    /**
     * @return void
     */
    public function testItUpdatesDeactivatedUsers(): void
    {
        $deactivationDays = config('norma.user.lifecycle.deactivate_when.invitations_days_reach');

        $this->travelTo(now()->subDays($deactivationDays + 3));

        $user = User::factory()->create([
            'user_type' => UserType::customer()->value,
            'lifecycle_stage' => LifecycleStage::invitationSent()->value,
        ]);

        $settings = $user->settings;
        $settings['last_invite_sent'] = now()->toDateString();
        $user->settings = $settings;
        $user->save();

        $user->refresh();

        $this->assertSame(now()->toDateString(), $user->settings['last_invite_sent']);

        $this->travelBack();

        $this->assertSame($user->lifecycle_stage, LifecycleStage::invitationSent()->value);

        Artisan::call('norma:review-lifecycles');

        $this->assertSame($user->refresh()->lifecycle_stage, LifecycleStage::invitationDeclined()->value);
    }

    /**
     * @return void
     */
    public function testItDeactivatesUsers(): void
    {
        $deactivationDays = config('norma.user.lifecycle.deactivate_when.inactivity_months_reach');

        $this->travelTo(now()->subMonths($deactivationDays + 1));

        $user = User::factory()->create([
            'user_type' => UserType::customer()->value,
            'lifecycle_stage' => LifecycleStage::active()->value,
        ]);

        $this->travelBack();

        $this->assertSame($user->lifecycle_stage, LifecycleStage::active()->value);

        Artisan::call('norma:review-lifecycles');

        $this->assertSame($user->refresh()->lifecycle_stage, LifecycleStage::deactivatedInactivity()->value);
    }
}
