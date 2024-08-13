<?php

namespace App\Http\Controllers\Auth\SAML;

use App\Exceptions\SAMLException;
use App\Services\Auth\SAML\SAMLClient;
use App\Traits\Auth\UsesSAMLAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SAMLSessionController
{
    use UsesSAMLAuthentication;

    /**
     * Display the initial login page.
     *
     * @param string $slug
     *
     * @return \Illuminate\View\View
     */
    public function index(string $slug): View
    {
        $this->findIdentityProviderBySlug($slug);

        /** @var View */
        return view('auth.saml.login');
    }

    /**
     * Redirect the user to the provider.
     *
     * @param \App\Services\Auth\SAML\SAMLClient $client
     * @param string                             $slug
     *
     * @throws \OneLogin\Saml2\Error
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(SAMLClient $client, string $slug): RedirectResponse
    {
        $provider = $this->findIdentityProviderBySlug($slug);

        return $client->login($provider);
    }

    /**
     * Attempt login via SAML.
     *
     * @param \App\Services\Auth\SAML\SAMLClient $client
     * @param string                             $slug
     *
     * @codeCoverageIgnore
     *
     * @throws \OneLogin\Saml2\Error
     * @throws \OneLogin\Saml2\ValidationError
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SAMLClient $client, string $slug): RedirectResponse
    {
        $provider = $this->findIdentityProviderBySlug($slug);

        try {
            $response = $client->assert($provider);
        } catch (SAMLException) {
            session()->flash('saml_failed_error', 'Authentication Failed. Please contact support.');

            return redirect()->route('my.saml.login.index', ['slug' => $slug]);
        }

        $fields = $this->extractUserFieldsFromSAMLResponse($response);

        if (count($fields) < 4) {
            session()->flash('saml_failed_error', __('auth.saml.missing_fields'));

            return redirect()->route('my.saml.login.index', ['slug' => $slug]);
        }

        $user = $this->updateSAMLUser($provider, $fields, $slug);

        if ($user instanceof RedirectResponse) {
            return $user;
        }

        return $this->loginSAMLUser($user);
    }

    /**
     * Handle the Single Logout.
     *
     * @param \Illuminate\Http\Request           $request
     * @param \App\Services\Auth\SAML\SAMLClient $client
     * @param string                             $slug
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, SAMLClient $client, string $slug): RedirectResponse
    {
        $provider = $this->findIdentityProviderBySlug($slug);

        $this->logoutSAMLUser($request);

        try {
            $client->logout($provider);
        } catch (Throwable) {
        }

        return redirect()->route('my.saml.login.index', ['slug' => $slug]);
    }
}
