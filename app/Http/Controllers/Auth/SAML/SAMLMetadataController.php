<?php

namespace App\Http\Controllers\Auth\SAML;

use App\Services\Auth\SAML\SAMLClient;
use App\Traits\Auth\UsesSAMLAuthentication;
use Illuminate\Http\Response;

class SAMLMetadataController
{
    use UsesSAMLAuthentication;

    /**
     * @param \App\Services\Auth\SAML\SAMLClient $client
     * @param string                             $slug
     *
     * @throws \OneLogin\Saml2\Error
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SAMLClient $client, string $slug): Response
    {
        $provider = $this->findIdentityProviderBySlug($slug);

        return response($client->metadata($provider), 200, ['Content-Type' => 'text/xml']);
    }
}
