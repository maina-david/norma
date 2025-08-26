<?php

namespace App\Http\Controllers\Auth\My;

use App\Http\Controllers\Controller;
use App\Managers\WhiteLabelManager;
use App\Models\Auth\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SSOLoginController extends Controller
{
    public function __construct(protected WhiteLabelManager $whiteLabelManager)
    {
    }

    /**
     * Login user to Norma given code from provider.
     */
    public function fromProviderCode(): RedirectResponse
    {
        $providerName = $this->getProviderName();

        if ($providerName === 'default') {
            // @codeCoverageIgnoreStart
            return redirect()->route('login');
            // @codeCoverageIgnoreEnd
        }

        $normaUser = $this->getNormaUser($providerName);

        Auth::login($normaUser);

        /** @var RedirectResponse */
        return redirect('/');
    }

    /**
     * @param string $providerName
     *
     * @return User
     */
    private function getNormaUser(string $providerName): User
    {
        /** @var AbstractProvider */
        $provider = Socialite::driver($providerName);
        $user = $provider->stateless()->user();

        $email = $user->getEmail();
        /** @var string */
        $authError = __('auth.error_oauth');
        if ($email === '') {
            // @codeCoverageIgnoreStart
            abort(403, $authError);
            // @codeCoverageIgnoreEnd
        }
        /** @var User|null */
        $normaUser = User::where('email', $email)->first();

        if (!$normaUser) {
            // @codeCoverageIgnoreStart
            abort(403, $authError);
            // @codeCoverageIgnoreEnd
        }

        return $normaUser;
    }

    /**
     * Redirects the user to the OAuth Provider.
     *
     * @return RedirectResponse
     */
    public function redirect(): RedirectResponse
    {
        $provider = $this->getProviderName();

        /** @var RedirectResponse */
        return $provider === 'default'
            ? redirect()->route('login')
            : Socialite::driver($provider)->redirect();
    }

    /**
     * @return string
     */
    protected function getProviderName(): string
    {
        $whitelabel = $this->whiteLabelManager->current();

        return $whitelabel->shortname();
    }

    /**
     * API Route:
     * Get access token by provider code - Rubicon uses this.
     *
     * @param string $provider
     *
     * @return JsonResponse
     */
    public function tokenFromProviderCode(string $provider): JsonResponse
    {
        $normaUser = $this->getNormaUser($provider);

        $token = $normaUser
            ->createToken('provider_code')
            ->accessToken;

        return response()->json(['access_token' => $token]);
    }
}
