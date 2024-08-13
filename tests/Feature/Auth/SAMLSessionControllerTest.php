<?php

namespace Tests\Feature\Auth;

use App\Enums\System\OrganisationModules;
use App\Livewire\Auth\LoginForm;
use App\Models\Auth\IdentityProvider;
use App\Models\Auth\User;
use App\Services\Auth\SAML\SAMLClient;
use Livewire;
use Mockery\MockInterface;
use OneLogin\Saml2\Constants;
use Tests\Feature\My\MyTestCase;

class SAMLSessionControllerTest extends MyTestCase
{
    public function testAuthenticationFlow(): void
    {
        $this->assertGuest();

        $user = User::factory()->create();
        $provider = IdentityProvider::factory()->create();
        $provider->load(['organisation']);
        $provider->organisation->updateSetting('modules.' . OrganisationModules::SSO->value, true);

        $this->withoutExceptionHandling()
            ->get(route('my.saml.login.index', ['slug' => $provider->organisation->slug]))
            ->assertSuccessful()
            ->assertSee('Log in');

        $response = $this->withoutExceptionHandling()
            ->get(route('my.saml.login.start', ['slug' => $provider->organisation->slug]))
            ->assertRedirect();

        $this->assertTrue(str_starts_with($response->headers->get('Location'), $provider->sso_url));

        $email = $this->faker->safeEmail();

        $saml = [
            'samlUserdata' => [
                'email' => [$email],
                'first_name' => [$this->faker->firstName()],
            ],
            'samlNameId' => $this->faker->uuid(),
            'samlNameIdFormat' => Constants::NAMEID_PERSISTENT,
            'samlNameIdNameQualifier' => '',
            'samlNameIdSPNameQualifier' => '',
            'samlSessionIndex' => $this->faker->uuid(),
        ];

        $this->partialMock(SAMLClient::class, function (MockInterface $mock) use ($saml) {
            $mock->shouldReceive([
                'assert' => $saml,
            ]);
        });

        $this->assertGuest();

        $this->assertDatabaseMissing(User::class, ['email' => $saml['samlUserdata']['email'][0]]);

        $this->withoutExceptionHandling()
            ->post(route('my.saml.login.complete', ['slug' => $provider->organisation->slug]))
            ->assertSessionHas('saml_failed_error')
            ->assertRedirect(route('my.saml.login.index', ['slug' => $provider->organisation->slug]));

        $this->assertGuest();

        $this->assertDatabaseMissing(User::class, ['email' => $saml['samlUserdata']['email'][0]]);

        $saml['samlUserdata']['last_name'] = [$this->faker->lastName()];

        $this->partialMock(SAMLClient::class, function (MockInterface $mock) use ($saml) {
            $mock->shouldReceive([
                'assert' => $saml,
            ]);
        });

        $this->assertGuest();

        $this->assertDatabaseMissing(User::class, ['email' => $saml['samlUserdata']['email'][0]]);

        // Test user with password auth.
        $existing = User::factory()->create(['email' => $saml['samlUserdata']['email'][0]]);

        $this->withoutExceptionHandling()
            ->post(route('my.saml.login.complete', ['slug' => $provider->organisation->slug]))
            ->assertSessionHas('saml_response')
            ->assertRedirect(route('my.saml.conflict.create', ['slug' => $provider->organisation->slug]));

        $this->assertGuest();

        // Test user with different provider details
        $otherProvider = IdentityProvider::factory()->create();
        $existing->forceFill([
            'identity_provider_id' => $otherProvider->id,
            'identity_provider_name_id' => $this->faker->uuid(),
        ])->save();

        $this->withoutExceptionHandling()
            ->post(route('my.saml.login.complete', ['slug' => $provider->organisation->slug]))
            ->assertSessionHas('saml_failed_error')
            ->assertRedirect(route('my.saml.login.index', ['slug' => $provider->organisation->slug]));

        $this->assertGuest();

        // Test another user existing via provider
        $providerUser = User::factory()->create([
            'identity_provider_id' => $provider->id,
            'identity_provider_name_id' => $saml['samlNameId'],
        ]);

        $this->withoutExceptionHandling()
            ->post(route('my.saml.login.complete', ['slug' => $provider->organisation->slug]))
            ->assertSessionHas('saml_failed_error')
            ->assertRedirect(route('my.saml.login.index', ['slug' => $provider->organisation->slug]));

        $this->assertGuest();

        // finally, log in the user.
        $existing->forceDelete();
        $providerUser->forceDelete();

        $this->assertGuest();

        $this->assertDatabaseMissing(User::class, ['email' => $saml['samlUserdata']['email'][0]]);

        $this->withoutExceptionHandling()
            ->post(route('my.saml.login.complete', ['slug' => $provider->organisation->slug]))
            ->assertRedirect(url('/'));

        $this->assertDatabaseHas(User::class, ['email' => $saml['samlUserdata']['email'][0]]);

        $this->assertAuthenticated();

        $this->withoutExceptionHandling()
            ->post(route('my.saml.logout', ['slug' => $provider->organisation->slug]))
            ->assertRedirect(route('my.saml.login.index', ['slug' => $provider->organisation->slug]));

        $this->assertGuest();

        Livewire::test(LoginForm::class)
            ->assertSee('Next')
            ->set('email', 'test@example.com')
            ->call('save')
            ->assertSee('Log in');

        Livewire::test(LoginForm::class)
            ->assertSee('Next')
            ->set('email', $email)
            ->call('save')
            ->assertRedirect();
    }
}
