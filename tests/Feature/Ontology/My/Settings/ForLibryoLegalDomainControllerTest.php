<?php

namespace Tests\Feature\Ontology\My\Settings;

use App\Models\Customer\Libryo;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Settings\SettingsTestCase;

class ForLibryoLegalDomainControllerTest extends SettingsTestCase
{
    /**
     * @return void
     */
    public function testItRendersTheIndexCorrecly(): void
    {
        $libryo = Libryo::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();
        $libryo->legalDomains()->attach($domains->pluck('id')->toArray());

        $route = route('my.settings.libryos.compilation.legal-domains.index', ['libryo' => $libryo]);

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
        $libryo = Libryo::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();

        $route = route('my.settings.legal-domains.for.libryo.add', ['libryo' => $libryo]);

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
        $libryo = Libryo::factory()->create();
        $domains = LegalDomain::factory()->count(4)->create();
        $libryo->legalDomains()->attach($domains->pluck('id')->toArray());

        $route = route('my.settings.legal-domains.for.libryo.actions', ['libryo' => $libryo]);

        $remove = $domains->splice(0, 2);
        $payload = $remove->mapWithKeys(fn ($domain) => ["actions-checkbox-{$domain->id}" => true])->toArray();
        $payload['action'] = 'remove_from_libryo';

        $this->assertForbiddenForNonAdmin($route, 'post');

        $this->signIn($this->mySuperUser());

        $response = $this->followingRedirects()
            ->post($route, $payload)
            ->assertSuccessful();

        $domains->each(fn ($domain) => $response->assertSee($domain->title));
        $remove->each(fn ($domain) => $response->assertDontSee($domain->title));
    }
}
