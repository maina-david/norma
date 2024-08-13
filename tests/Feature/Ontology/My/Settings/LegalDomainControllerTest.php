<?php

namespace Tests\Feature\Ontology\My\Settings;

use App\Models\Ontology\LegalDomain;
use Tests\Feature\Settings\SettingsTestCase;

class LegalDomainControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItOnlyReturnsJson(): void
    {
        $domains = LegalDomain::factory()->count(2)->create();

        $this->assertForbiddenForNonAdmin(route('my.settings.legal-domains.index'), 'get');

        $this->signIn($this->mySuperUser());

        $this->get(route('my.settings.legal-domains.index'))
            ->assertNotFound();

        $this->json('GET', route('my.settings.legal-domains.index', ['search' => $domains->first()->title]))
            ->assertSuccessful();
    }
}
