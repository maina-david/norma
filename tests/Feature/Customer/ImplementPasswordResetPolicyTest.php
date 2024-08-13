<?php

namespace Tests\Feature\Customer;

use App\Jobs\Customer\ImplementPasswordResetPolicy;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ImplementPasswordResetPolicyTest extends TestCase
{
    /**
     * @return void
     */
    public function testItTriggersPasswordResetInAnOrganisation(): void
    {
        $notImplemented = Organisation::factory()->create();
        $notImplemented->updateSetting('policies.password_reset.enabled', false);
        $notImplemented->updateSetting('policies.password_reset.frequency', 1);

        $implemented = Organisation::factory()->create();
        $implemented->updateSetting('policies.password_reset.enabled', true);
        $implemented->updateSetting('policies.password_reset.frequency', 1);

        $notImplementedUsers = User::factory()->count(2)->create();
        $notImplementedUsers->each(function ($user) use ($notImplemented) {
            $user->organisations()->attach($notImplemented->id);

            $this->assertFalse($user->refresh()->password_reset);
        });

        $implementedUsers = User::factory()->count(2)->create();
        $implementedUsers->each(function ($user) use ($implemented) {
            $user->organisations()->attach($implemented->id);

            $this->assertFalse($user->refresh()->password_reset);
        });

        $this->assertNull(Arr::get($notImplemented->refresh()->settings, 'policies.password_reset.last_implemented'));
        $this->assertNull(Arr::get($implemented->refresh()->settings, 'policies.password_reset.last_implemented'));

        ImplementPasswordResetPolicy::dispatch();

        $this->assertNull(Arr::get($notImplemented->refresh()->settings, 'policies.password_reset.last_implemented'));
        $this->assertNotNull(Arr::get($implemented->refresh()->settings, 'policies.password_reset.last_implemented'));

        $notImplementedUsers->each(function ($user) {
            $this->assertFalse($user->refresh()->password_reset);
        });

        $implementedUsers->each(function ($user) {
            $this->assertTrue($user->refresh()->password_reset);
        });
    }
}
