<?php

namespace App\Services\Auth\SAML;

use App\Exceptions\SAMLException;
use App\Models\Auth\IdentityProvider;
use App\Models\Customer\Organisation;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Constants;
use OneLogin\Saml2\Settings;

class SAMLClient
{
    /**
     * Get the default SAML Config.
     *
     * @param \App\Models\Customer\Organisation $organisation
     *
     * @return array<string, mixed>
     */
    protected function getDefaultConfig(Organisation $organisation): array
    {
        return [
            'debug' => false,
            'strict' => true,
            'baseurl' => route('my.saml.login.index', ['slug' => $organisation->slug]),
            'wantMessagesSigned' => true,
            'wantAssertionsEncrypted' => true,
            'wantAssertionsSigned' => true,
            'wantXMLValidation' => true,
            'wantNameIdEncrypted' => true,
            'compress' => [
                'requests' => true,
                'responses' => true,
            ],
            'organization' => [
                'en-GB' => [
                    'name' => 'Libryo LTD',
                    'displayname' => 'Libryo LTD',
                    'url' => 'https://libryo.com',
                ],
            ],
            'sp' => [
                'entityId' => route('my.saml.metadata.index', ['slug' => $organisation->slug]),
                'assertionConsumerService' => [
                    'url' => route('my.saml.login.complete', ['slug' => $organisation->slug]),
                ],
                'singleLogoutService' => [
                    'url' => route('my.saml.logout', ['slug' => $organisation->slug]),
                ],
                'attributeConsumingService' => [
                    'serviceName' => 'Libryo LTD',
                    'serviceDescription' => 'Libryo LTD Single Sign-on Service',
                    'requestedAttributes' => [
                        [
                            'name' => 'first_name',
                            'isRequired' => true,
                            'friendlyName' => 'First Name',
                        ],
                        [
                            'name' => 'last_name',
                            'isRequired' => true,
                            'friendlyName' => 'Last Name',
                        ],
                        [
                            'name' => 'email',
                            'isRequired' => true,
                            'friendlyName' => 'Email',
                        ],
                    ],
                ],
                'NameIDFormat' => Constants::NAMEID_PERSISTENT,
            ],
            'security' => [
                'wantNameId' => true,
                'requestedAuthnContext' => false,
            ],
        ];
    }

    /**
     * Get the SAML config.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     *
     * @return array<string, mixed>
     */
    public function getConfig(IdentityProvider $provider): array
    {
        $provider->load(['organisation']);

        return [
            ...$this->getDefaultConfig($provider->organisation),

            'idp' => [
                'entityId' => $provider->entity_id,
                'singleSignOnService' => [
                    'url' => $provider->sso_url,
                ],
                'singleLogoutService' => [
                    'url' => $provider->slo_url ?? '',
                ],
                'x509cert' => $provider->certificate,
            ],
        ];
    }

    /**
     * Get the auth engine.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     *
     * @throws \OneLogin\Saml2\Error
     *
     * @return \OneLogin\Saml2\Auth
     */
    public function auth(IdentityProvider $provider): Auth
    {
        return new Auth($this->getConfig($provider));
    }

    /**
     * Login the user using the provided identity provider.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     *
     * @throws \OneLogin\Saml2\Error
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(IdentityProvider $provider): RedirectResponse
    {
        Config::set('session.same_site', 'none');

        $auth = $this->auth($provider);

        /** @var string $ssoBuiltUrl */
        $ssoBuiltUrl = $auth->login(null, [], false, false, true);

        abort_unless((bool) $ssoBuiltUrl, 404);

        session()->put('AuthNRequestID', $auth->getLastRequestID());

        return redirect()->away(
            path: $ssoBuiltUrl,
            headers: [
                'Pragma' => 'no-cache',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]
        );
    }

    /**
     * Logout the given user.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     *
     * @codeCoverageIgnore
     *
     * @throws \OneLogin\Saml2\Error
     *
     * @return void
     */
    public function logout(IdentityProvider $provider): void
    {
        $requestId = session('LogoutRequestID');
        $auth = $this->auth($provider);
        $auth->processSLO(false, $requestId);

        $this->validateAndThrowErrors($auth); // @phpstan-ignore-line
    }

    /**
     * Check if errors exist and throw them.
     *
     * @codeCoverageIgnore
     *
     * @param \OneLogin\Saml2\Auth $auth
     *
     * @throws \App\Exceptions\SAMLException
     *
     * @return void
     */
    protected function validateAndThrowErrors(Auth $auth): void
    {
        $errors = $auth->getErrors();

        if (!empty($errors)) {
            $errors = implode(', ', $errors);
            if ($auth->getSettings()->isDebugActive()) {
                $errors .= ',' . $auth->getLastErrorReason();
            }

            debug_log($errors . ', ' . $auth->getLastErrorReason());

            throw new SAMLException($errors);
        }
    }

    /**
     * Validate the given auth assertion.
     *
     * @codeCoverageIgnore
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     *
     * @throws \App\Exceptions\SAMLException
     * @throws \OneLogin\Saml2\Error
     * @throws \OneLogin\Saml2\ValidationError
     *
     * @return array<string, mixed>|null
     */
    public function assert(IdentityProvider $provider): ?array
    {
        $requestId = session('AuthNRequestID');
        $auth = $this->auth($provider);

        $auth->processResponse($requestId);

        $this->validateAndThrowErrors($auth);

        if (!$auth->isAuthenticated()) {
            return null;
        }

        session()->forget('AuthNRequestID');

        return [
            'samlUserdata' => $auth->getAttributes(),
            'samlNameId' => $auth->getNameId(),
            'samlNameIdFormat' => $auth->getNameIdFormat(),
            'samlNameIdNameQualifier' => $auth->getNameIdNameQualifier(),
            'samlNameIdSPNameQualifier' => $auth->getNameIdSPNameQualifier(),
            'samlSessionIndex' => $auth->getSessionIndex(),
        ];
    }

    /**
     * Get the metadata for the Service Provider.
     *
     * @param \App\Models\Auth\IdentityProvider $provider
     *
     * @throws \OneLogin\Saml2\Error
     * @throws Exception
     *
     * @return string
     */
    public function metadata(IdentityProvider $provider): string
    {
        $settings = new Settings($this->getConfig($provider), true);

        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);

        return empty($errors) ? $metadata : '';
    }
}
