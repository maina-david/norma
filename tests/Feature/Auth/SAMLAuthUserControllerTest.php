<?php

namespace Tests\Feature\Auth;

use App\Enums\System\OrganisationModules;
use App\Models\Auth\IdentityProvider;
use App\Models\Auth\User;
use Tests\Feature\My\MyTestCase;

class SAMLAuthUserControllerTest extends MyTestCase
{
    public function testShowingAndEnablingSSO(): void
    {
        $this->assertGuest();

        $user = User::factory()->create();
        $provider = IdentityProvider::factory()->create();
        $provider->load(['organisation']);
        $provider->organisation->updateSetting('modules.' . OrganisationModules::SSO->value, true);

        $this->withExceptionHandling()
            ->get(route('my.saml.conflict.create', ['slug' => $provider->organisation->slug]))
            ->assertSuccessful()
            ->assertSee('Enable SSO');

        $saml = [
            'norma_user_id' => $user->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $user->email,
            'name_id' => $this->faker->uuid(),
        ];

        $this->assertNull($user->identity_provider_name_id);
        $this->assertNull($user->identity_provider_id);

        $this->withSession(['saml_response' => $saml])
            ->post(route('my.saml.conflict.store', ['slug' => $provider->organisation->slug]))
            ->assertRedirect();

        $this->assertAuthenticated();
    }
}
