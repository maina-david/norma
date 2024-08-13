<?php

namespace App\OAuth\SSO;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\InvalidStateException;

abstract class AbstractJsonOAuth2Provider extends AbstractOAuth2Provider
{
    /**
     * Get the access token response for the given code.
     *
     * @param string $code
     *
     * @return array<mixed>
     */
    public function getAccessTokenResponse($code)
    {
        $fields = $this->getTokenFields($code);
        $authFields = [
            'clientId' => $fields['client_id'],
            'clientSecret' => $fields['client_secret'],
            'code' => $fields['code'],
        ];

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::JSON => $authFields,
            RequestOptions::VERIFY => env('GUZZLE_VERIFY_SSL', true),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->user) {
            // @codeCoverageIgnoreStart
            return $this->user;
            // @codeCoverageIgnoreEnd
        }

        if ($this->hasInvalidState()) {
            // @codeCoverageIgnoreStart
            throw new InvalidStateException();
            // @codeCoverageIgnoreEnd
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        $this->user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->parseAccessToken($response)
        ));

        return $this->user->setToken($token);
        // ->setRefreshToken(Arr::get($response, 'refresh_token'))
        // ->setExpiresIn(Arr::get($response, 'expires_in'));
    }

    /**
     * Get the access token from the token response body.
     *
     * @param array<mixed> $body
     *
     * @return string
     */
    protected function parseAccessToken(array $body)
    {
        /** @var string */
        return Arr::get($body, 'authorization');
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(config('services.sso.' . $this->getIdentifier() . '.user_url'), [
            'headers' => [
                'Authorization' => $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
