<?php

namespace Tests\Feature\Auth;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Providers\RouteServiceProvider;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testLoginScreenCanBeRendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function testUsersCanAuthenticateUsingTheLoginScreen(): void
    {
        $user = User::factory()->create();
        $this->mySuperUser($user);
        $organisation = Organisation::factory()->create();
        $normas = Norma::factory(2)->create();
        $user->normas()->attach($normas->modelKeys());
        $user->organisations()->attach($organisation);

        $manager = app(ActiveNormasManager::class);
        $manager->activate($user, $normas[0]);
        $manager->activate($user, $normas[1]);

        $this->flushSession();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();

        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect();

        $this->assertGuest();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        // testing that SetActiveNormaAfterLogin is called
        $active = $manager->getActive($user);
        $this->assertSame($normas[1]->id, $active->id);

        $this->get(RouteServiceProvider::HOME)->assertSuccessful()->assertSee('Hello');

        $user->active = false;
        $user->save();

        $this->actingAs($user)->get('/legal-updates')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /**
     * @return void
     */
    public function testActiveNormaAfterLogin(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $norma = Norma::factory()->create();
        $user->normas()->attach($norma->id);
        $user->organisations()->attach($organisation);

        $manager = app(ActiveNormasManager::class);
        $manager->activate($user, $norma);

        $this->flushSession();
        $user->normas()->detach($norma);

        $response = $this->withExceptionHandling()->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    /**
     * @return void
     */
    public function testActiveNormaAfterFirstLogin(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $norma = Norma::factory()->create();
        $this->mySuperUser($user);
        $user->normas()->attach($norma->id);
        $user->organisations()->attach($organisation);

        $response = $this->withExceptionHandling()->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSuccessful()
            ->assertSee($norma->title);
    }

    /**
     * @return void
     */
    public function testUsersCanNotAuthenticateWithInvalidPassword(): void
    {
        Config::set('services.google_recaptcha.enabled', true);

        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $this->get('/login')->assertSee('g-recaptcha');

        Http::fake([
            'www.google.com/*' => Http::sequence()
                ->push(
                    body: [
                        'success' => false,
                        'challenge_ts' => '2023-11-08T09:24:23Z',
                        'hostname' => 'my.monolith.test',
                    ],
                    headers: ['content-type' => 'application/json']
                )
                ->push(
                    body: [
                        'success' => true,
                        'challenge_ts' => '2023-11-08T09:24:23Z',
                        'hostname' => 'my.monolith.test',
                    ],
                    headers: ['content-type' => 'application/json']
                ),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'invalid-response',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'valid-response',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertAuthenticated();
    }
}
