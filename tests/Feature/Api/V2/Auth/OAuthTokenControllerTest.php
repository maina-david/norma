<?php

namespace Tests\Feature\Api\V2\Auth;

use App\Models\Auth\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OAuthTokenControllerTest extends TestCase
{
    public function testIssueToken(): void
    {
        $secret = 'secret';
        $client = Passport::client();
        $client->secret = $secret;
        $client->name = 'Test Client';
        $client->redirect = '';
        $client->personal_access_client = 0;
        $client->password_client = 1;
        $client->revoked = 0;
        $client->save();
        $routeName = 'api.v2.passport.oauth.issue.token';
        $route = route($routeName);
        $password = 'TestPassword!@#$';
        $user = User::factory()->create(['password' => Hash::make($password)]);
        $data = [
            'grant_type' => 'password',
            'username' => $user->email,
            'password' => $password,
            'client_id' => $client->id,
            'client_secret' => $secret,
        ];
        $response = $this->json('post', $route, $data);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'access_token', 'expires_in', 'refresh_token',
        ]);

        $data['password'] = Str::random();
        $response = $this->withExceptionHandling()->json('post', $route, $data);
        $response->assertUnauthorized();

        $data['username'] = Str::random();
        $response = $this->withExceptionHandling()->json('post', $route, $data);
        $response->assertNotFound();
    }
}
