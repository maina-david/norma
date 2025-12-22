<?php

namespace Tests\Feature\Auth;

use App\Enums\Application\ApplicationType;
use App\Managers\WhiteLabelManager;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Partners\WhiteLabel;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Mockery\MockInterface;
use Tests\Feature\My\MyTestCase;

class SSOLoginControllerTest extends MyTestCase
{
    private function mockWhitelabel(string $shortName = 'regwatch'): void
    {
        $whitelabel = WhiteLabel::factory()->create(['shortname' => $shortName]);
        $this->mock(
            WhiteLabelManager::class,
            function (MockInterface $mock) use ($whitelabel) {
                $mock->allows([
                    'current' => $whitelabel,
                    'activate' => null,
                    'isValid' => true,
                ]);
            }
        );
    }

    public function runTestFromProviderCode(string $providerName): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $role = Role::factory()->my()->create(['title' => 'My Users', 'app' => ApplicationType::my()->value]);

        $this->mockWhitelabel($providerName);

        $routeName = 'my.oauth.provider.callback';

        $route = route($routeName, ['code' => Str::random()]);
        $route = str_replace('http://', 'http://' . $providerName . '.', $route);

        $userCount = User::count();
        $this->followingRedirects()
            ->get($route)
            ->assertSuccessful();

        // a new user should be created
        $this->assertGreaterThan($userCount, User::count());

        $newUser = User::with(['organisations', 'normas'])->get()->last();
        $this->assertSame($providerName, $newUser->auth_provider);
        $this->assertSame(config('services.sso.' . $providerName . '.partner_id'), $newUser->organisations->first()->partner_id);
        $this->assertGreaterThan(0, $newUser->normas->count());
    }

    public function testFromProviderCodeRegwatch(): void
    {
        $this->runTestFromProviderCode('regwatch');
    }

    public function testFromProviderCodeXGRC(): void
    {
        $this->runTestFromProviderCode('xgrc');
    }

    public function testRedirect(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.oauth.provider.redirect';
        $response = $this->get(route($routeName))->assertRedirect('login');
    }

    public function testRedirectWhitelabel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.oauth.provider.redirect';
        $this->mockWhitelabel('regwatch');
        $response = $this->get(route($routeName))
            ->assertRedirectContains('rubicon.com')
            ->assertRedirectContains('clientId=' . urlencode(config('services.sso.regwatch.client_id')))
            ->assertRedirectContains('redirectUri=')
            ->assertRedirectContains('response_type=code');
    }

    public function testTokenFromProviderCode(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $role = Role::factory()->my()->create(['title' => 'My Users', 'app' => ApplicationType::my()->value]);
        $cli = Passport::client();
        $cli->name = 'Test Client';
        $cli->redirect = '';
        $cli->secret = 'secret';
        $cli->personal_access_client = 1;
        $cli->password_client = 0;
        $cli->revoked = 0;
        $cli->save();

        $client = Passport::personalAccessClient();
        $client->forceFill(['client_id' => $cli->id]);
        $client->save();

        $this->mockWhitelabel('regwatch');

        $routeName = 'api.v1.attempt.provider.login';
        $route = route($routeName, ['provider' => 'regwatch', 'code' => Str::random()]);

        $response = $this->json('get', $route);
        $response->assertSuccessful();
    }
}
