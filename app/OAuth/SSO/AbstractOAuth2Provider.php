<?php

namespace App\OAuth\SSO;

use App\Models\Auth\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractOAuth2Provider extends AbstractProvider implements ProviderInterface
{
    abstract protected function getIdentifier(): string;

    /**
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(config('services.sso.' . $this->getIdentifier() . '.authorize_url'), $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return config('services.sso.' . $this->getIdentifier() . '.token_url');
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     *
     * @return array<mixed>
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(config('services.sso.' . $this->getIdentifier() . '.user_url'), [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @param array<string, mixed> $user
     *
     * @codeCoverageIgnore
     *
     * @return SocialiteUser
     */
    protected function mapUserToObject(array $user)
    {
        return (new SocialiteUser())->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['username'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $user['avatar'],
        ]);
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     * Had to override it as there was no other way of testing this.
     *
     * @return Client
     */
    protected function getHttpClient()
    {
        // @codeCoverageIgnoreStart
        if (App::environment('testing')) {
            return $this->mockHttpClient();
        }

        return parent::getHttpClient();
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return Client
     */
    protected function mockHttpClient(): Client
    {
        // @codeCoverageIgnoreStart
        if (method_exists($this, 'testUserData')) {
            $userData = $this->testUserData();
        } else {
            $user = User::factory()->make();
            $userData = $user->toArray();
        }

        /** @var MockInterface $tokenMock */
        $tokenMock = Mockery::mock(ResponseInterface::class);
        $tokenMock->allows(['getBody' => json_encode(['authorization' => 'token'])]);
        /** @var MockInterface $userMock */
        $userMock = Mockery::mock(ResponseInterface::class);
        $userMock->allows(['getBody' => json_encode($userData)]);
        /** @var MockInterface $mockClient */
        $mockClient = Mockery::mock(Client::class);
        $mockClient->allows([
            'post' => $tokenMock,
            'get' => $userMock,
        ]);

        /** @var Client */
        return $mockClient;
        // @codeCoverageIgnoreEnd
    }
}
