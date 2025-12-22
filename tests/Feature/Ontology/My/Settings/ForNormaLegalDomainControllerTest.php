<?php

namespace Tests\Feature\Ontology\My\Settings;

use App\Models\Customer\Norma;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Settings\SettingsTestCase;

class ForNormaLegalDomainControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItRendersTheIndexCorrecly(): void
    {
        $norma = Norma::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();
        $norma->legalDomains()->attach($domains->pluck('id')->toArray());

        $route = route('my.settings.normas.compilation.legal-domains.index', ['norma' => $norma]);

        $this->assertForbiddenForNonAdmin($route, 'get');

        $this->signIn($this->mySuperUser());

        $response = $this->get($route)->assertSuccessful();

        $domains->each(fn ($domain) => $response->assertSee($domain->title));
    }

    /**
     * @return void
     */
    public function testItAttachesCorrecly(): void
    {
        $norma = Norma::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();

        $route = route('my.settings.legal-domains.for.norma.add', ['norma' => $norma]);

        $this->assertForbiddenForNonAdmin($route, 'post');

        $this->signIn($this->mySuperUser());

        $response = $this->followingRedirects()
            ->post($route, ['legal-domains' => $domains->pluck('id')->toArray()])
            ->assertSuccessful();

        $domains->each(fn ($domain) => $response->assertSee($domain->title));
    }

    /**
     * @return void
     */
    public function testItDetachesCorrecly(): void
    {
        $norma = Norma::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();
        $norma->legalDomains()->attach($domains->pluck('id')->toArray());

        $route = route('my.settings.legal-domains.for.norma.actions', ['norma' => $norma]);

        $remove = $domains->splice(0, 2);
        $payload = $remove->mapWithKeys(fn ($domain) => ["actions-checkbox-{$domain->id}" => true])->toArray();
        $payload['action'] = 'remove_from_norma';

        $this->assertForbiddenForNonAdmin($route, 'post');

        $this->signIn($this->mySuperUser());

        $response = $this->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful();

        $domains->each(fn ($domain) => $response->assertSee($domain->title));
        $remove->each(fn ($domain) => $response->assertDontSee($domain->title));
    }
}
