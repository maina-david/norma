<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class OAuthTokenController extends AccessTokenController
{
    /**
     * Authorize a client to access the user's account.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Illuminate\Http\Response
     */
    public function issueToken(ServerRequestInterface $request)
    {
        $exclude = ['partner'];

        if (in_array(request('grant_type'), $exclude)) {
            // @codeCoverageIgnoreStart
            return parent::issueToken($request);
            // @codeCoverageIgnoreEnd
        }

        $this->handleUserValidation();

        return $this->_issueToken($request);
    }

    /**
     * Authorize a client to access the user's account.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Illuminate\Http\Response
     */
    private function _issueToken(ServerRequestInterface $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            return $this->convertResponse(
                $this->server->respondToAccessTokenRequest($request, new Psr7Response())
            );
        });
    }

    /**
     * Gets the user from the requests and validates their credentials.
     */
    private function handleUserValidation(): User
    {
        $userQuery = User::where('email', app(Request::class)->input('username'));

        if (request('grant_type') === 'password') {
            $this->validateUser($userQuery);
        }

        /** @var User */
        return $userQuery->firstOrFail();
    }

    /**
     * Validate the given user.
     *
     * @param Builder $userQuery
     */
    private function validateUser(Builder $userQuery): void
    {
        if (!$userQuery->exists()) {
            throw new NotFoundHttpException(__('exceptions.api.auth.user_not_exists'));
        }

        // ensure admin login credentials are valid
        if (!Auth::validate([
            'email' => app(Request::class)->input('username'),
            'password' => app(Request::class)->input('password'),
        ])) {
            throw new UnauthorizedHttpException(__('auth.failed'));
        }
    }
}
