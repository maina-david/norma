<?php

namespace App\OAuth\OAuth2;

use App\Models\Auth\User;
use DateInterval;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @codeCoverageIgnore
 */
class PartnerGrant extends ClientCredentialsGrant
{
    /**
     * @param UserRepositoryInterface         $userRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository
    ) {
        $this->setUserRepository($userRepository);
        $this->setRefreshTokenRepository($refreshTokenRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ) {
        /** @phpstan-ignore-next-line */
        $client = $this->getClientEntityOrFail(Client::first()->id, $request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request));
        $user = $this->validateUser($request);
        $this->validatePartner($request, $user);

        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->id, $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        $responseType->setAccessToken($accessToken);
        /* @phpstan-ignore-next-line */
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    /**
     * Validate that the partner exists.
     *
     * @param ServerRequestInterface $request
     * @param User                   $createFor
     *
     * @throws OAuthServerException
     *
     * @return User
     */
    protected function validatePartner(ServerRequestInterface $request, User $createFor): User
    {
        /** @var Request $laravelRequest */
        $laravelRequest = request();

        $user = $laravelRequest->user('api');

        if (!$user || !$user->isIntegrationUser()) {
            throw OAuthServerException::accessDenied('You are not authorised to generate tokens.');
        }

        $orgIds = $createFor->organisations()->where('partner_id', $user->partner_id)->select('id');
        $hasAccess = $user->organisations()
            ->wherePivot('is_admin', true)
            ->whereIn('id', $orgIds)
            ->exists();

        if (!$hasAccess) {
            throw OAuthServerException::accessDenied('You are not authorised to generate tokens for this user.');
        }

        return $user;
    }

    /**
     * Validate that the user exists.
     *
     * @param ServerRequestInterface $request
     *
     * @throws OAuthServerException
     *
     * @return mixed
     */
    protected function validateUser(ServerRequestInterface $request)
    {
        $user = $this->getRequestParameter('email', $request);

        if (!$user) {
            throw OAuthServerException::invalidRequest('email');
        }

        if (!$user = User::where('email', $user)->first()) {
            throw OAuthServerException::invalidRequest('email', 'User not found.');
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'partner';
    }
}
