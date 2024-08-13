<?php

namespace Tests\Feature\Auth\Settings;

use App\Managers\AppManager;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Tests\Feature\Settings\SettingsTestCase;

class PasswordResetControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItGeneratesCorrectLinks(): void
    {
        $user = $this->activateAllOrg();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);

        $user = User::factory()->create();
        $this->mySuperUser($user);
        $org = Organisation::factory()->create();
        $this->assertNotNull($org->whitelabel_id);
        $user->organisations()->attach($org);

        $this->withoutExceptionHandling()
            ->followingRedirects()
            ->post(route('my.settings.users.password-reset', ['user' => $user->id]), [])
            ->assertSuccessful()
            ->assertSee('Password Reset Link')
            ->assertSee(AppManager::appWhiteLabelURL($org->whitelabel->shortname) . '/reset-password/');

        $this->withoutExceptionHandling()
            ->followingRedirects()
            ->post(route('my.settings.users.password-reset', ['user' => $user->id]), [])
            ->assertSuccessful()
            ->assertSee('Password Reset Link')
            ->assertSee('Please wait before retrying.')
            ->assertDontSee(AppManager::appWhiteLabelURL($org->whitelabel->shortname) . '/reset-password/');
    }
}
