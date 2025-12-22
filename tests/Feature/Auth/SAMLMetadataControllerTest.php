<?php

namespace Tests\Feature\Auth;

use App\Enums\System\OrganisationModules;
use App\Models\Auth\IdentityProvider;
use Tests\Feature\My\MyTestCase;

class SAMLMetadataControllerTest extends MyTestCase
{
    public function testRenderingMetaData(): void
    {
        $provider = IdentityProvider::factory()->create();
        $provider->load(['organisation']);
        $provider->organisation->updateSetting('modules.' . OrganisationModules::SSO->value, true);

        $this->withoutExceptionHandling()
            ->get(route('my.saml.metadata.index', ['slug' => $provider->organisation->slug]))
            ->assertSuccessful()
            ->assertSee('Norma LTD');
    }
}
