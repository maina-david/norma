<?php

namespace App\Services\Auth;

use App\Models\Customer\Organisation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

/**
 * @codeCoverageIgnore
 */
class OrganisationOAuthManager
{
    use HandlesOAuthErrors;

    /**
     * Create a new instance.
     *
     * @param \Laravel\Passport\ClientRepository        $clients
     * @param \League\OAuth2\Server\AuthorizationServer $server
     */
    public function __construct(protected ClientRepository $clients, protected AuthorizationServer $server)
    {
    }

    /**
     * Generate access token for the given client.
     *
     * @param \App\Models\Customer\Organisation $organisation
     *
     * @throws \Laravel\Passport\Exceptions\OAuthServerException
     *
     * @return stdClass
     */
    public function generateToken(Organisation $organisation): stdClass
    {
        $this->server->enableGrantType(new ClientCredentialsGrant(), Carbon::now()->diff(now()->addYears(15)));
        $client = Client::where('organisation_id', $organisation->id)->where('revoked', false)->first();

        if (!$client) {
            $client = $this->clients->create(null, "{$organisation->title} Client", '');
            $client->forceFill(['organisation_id' => $organisation->id])->save();
        }

        $secret = Str::random(40);
        $client->update(['secret' => $secret]);

        $request = app(ServerRequestInterface::class)->withParsedBody([
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $secret,
            'scope' => '*',
        ]);

        /** @var \Nyholm\Psr7\Response $response */
        $response = $this->withErrorHandling(function () use ($request) {
            return $this->server->respondToAccessTokenRequest($request, new Psr7Response());
        });

        /** @var stdClass */
        return json_decode((string) $response->getBody());
    }
}
