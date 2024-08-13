<?php

namespace Tests\Feature\Console\Auth;

use Illuminate\Support\Str;
use Tests\TestCase;

class Verify2FATest extends TestCase
{
    public function testDisableNon2FAAdminUsers(): void
    {
        $active = $this->mySuperUser();
        $inactive = $this->mySuperUser();

        $active->update([
            'two_factor_secret' => Str::random(32),
            'two_factor_recovery_codes' => Str::random(32),
            'active' => true,
        ]);

        $inactive->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'active' => true,
        ]);

        $this->artisan('auth:disable-non-2fa-admins')
            ->assertSuccessful();

        $this->assertTrue($active->refresh()->active);
        $this->assertFalse($inactive->refresh()->active);
    }
}
