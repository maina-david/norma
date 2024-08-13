<?php

namespace Tests\Feature\Auth\Settings;

use App\Enums\System\OrganisationModules;
use App\Models\Auth\IdentityProvider;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Tests\Feature\Settings\SettingsTestCase;

class SSOPageControllerTest extends SettingsTestCase
{
    public function testViewingAndChangingSettings(): void
    {
        $org = Organisation::factory()->create();
        $team = Team::factory()->create(['organisation_id' => $org->id]);
        $route = route('my.settings.sso.edit');
        $user = $this->assertForbiddenForNonAdmin($route, 'get');

        $user->organisations()->attach($org, ['is_admin' => true]);

        $this->withExceptionHandling()
            ->get($route)
            ->assertNotFound();

        $org->updateSetting('modules.' . OrganisationModules::SSO->value, true);

        $this->withoutExceptionHandling()
            ->get($route)
            ->assertSuccessful();

        $payload = [
            'entity_id' => 'https://libryo.com',
            'slo_url' => 'https://libryo.com',
            'certificate' => 'https://libryo.com',
            'enabled' => 'https://libryo.com',
            'team_id' => $team->id,
            'sso_url' => 'https://libryo.com',
        ];

        $this->assertDatabaseMissing(IdentityProvider::class, ['organisation_id' => $org->id]);

        $this->withoutExceptionHandling()
            ->put(route('my.settings.sso.update'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(IdentityProvider::class, ['organisation_id' => $org->id]);
    }
}
