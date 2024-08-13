<?php

namespace App\Http\Controllers\Auth\SAML;

use App\Models\Auth\User;
use App\Traits\Auth\UsesSAMLAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SAMLAuthUserController
{
    use UsesSAMLAuthentication;

    /**
     * Display the conflict page.
     *
     * @param string $slug
     *
     * @return \Illuminate\View\View
     */
    public function create(string $slug): View
    {
        /** @var View */
        return view('auth.saml.conflict', ['slug' => $slug]);
    }

    /**
     * Attempt login.
     *
     * @param string $slug
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(string $slug): RedirectResponse
    {
        $provider = $this->findIdentityProviderBySlug($slug);
        $response = session()->get('saml_response');
        abort_unless($response, 404);

        /** @var User $user */
        $user = User::findOrFail($response['libryo_user_id']);

        $user = $this->updateSAMLUserDetails($provider, $user, $response);

        return $this->loginSAMLUser($user);
    }
}
