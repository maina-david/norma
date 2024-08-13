<?php

namespace App\OAuth\SSO\Providers;

use App\OAuth\SSO\AbstractOAuth2Provider;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\User as SocUser;

/**
 * @codeCoverageIgnore
 *
 * @see https://github.com/SocialiteProviders/Microsoft-Azure/blob/master/Provider.php
 */
abstract class AzureProvider extends AbstractOAuth2Provider
{
    /** Unique Provider Identifier. */
    public const IDENTIFIER = 'AZURE';

    /**
     * The base Azure Graph URL.
     *
     * @var string
     */
    protected $graphUrl = 'https://graph.microsoft.com/v1.0/me';

    /** {@inheritdoc} */
    protected $scopeSeparator = ' ';

    /**
     * The scopes being requested.
     *
     * @var array<string>
     */
    protected $scopes = ['User.Read'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getBaseUrl() . '/oauth2/v2.0/authorize', $state);
    }

    /**
     * Return the logout endpoint with post_logout_redirect_uri query parameter.
     *
     * @param string $redirectUri
     *
     * @return string
     */
    public function getLogoutUrl(string $redirectUri)
    {
        return $this->getBaseUrl()
            . '/oauth2/logout?'
            . http_build_query(['post_logout_redirect_uri' => $redirectUri], '', '&', $this->encodingType);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->getBaseUrl() . '/oauth2/v2.0/token';
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        // $this->credentialsResponseBody = json_decode((string) $response->getBody(), true);

        /** @var array<string, mixed> */
        $body = $response->getBody();

        return $this->parseAccessToken($body);
    }

    /**
     * Get the access token from the token response body.
     *
     * @param array<string, mixed> $body
     *
     * @return string
     */
    protected function parseAccessToken($body)
    {
        return Arr::get($body, 'access_token');
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->graphUrl, [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            // RequestOptions::PROXY => $this->getConfig('proxy'),
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new SocUser())->setRaw($user)->map([
            'id' => $user['id'],
            'fname' => $user['givenName'],
            'sname' => $user['surname'],
            'email' => $user['userPrincipalName'],
            'integration_id' => $user['id'],
        ]);
    }

    /**
     * Get the access token response for the given code.
     *
     * @param string $code
     *
     * @return array<string, mixed>
     */
    public function getAccessTokenResponse($code)
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => ['Accept' => 'application/json'],
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
            // RequestOptions::PROXY       => $this->getConfig('proxy'),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * @return string
     */
    protected function getBaseUrl(): string
    {
        $identifier = $this->getIdentifier();
        /** @var string */
        $tenantId = config('services.sso.' . $identifier . '.tenant_id');

        return 'https://login.microsoftonline.com/' . $tenantId;
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public static function additionalConfigKeys()
    // {
    //     return ['tenant', 'proxy'];
    // }
}
