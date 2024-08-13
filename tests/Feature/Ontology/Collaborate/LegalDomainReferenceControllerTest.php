<?php

namespace Tests\Feature\Ontology\Collaborate;

use App\Models\Corpus\Reference;
use App\Models\Ontology\LegalDomain;
use Tests\Feature\Traits\HasVisibleFieldAssertions;
use Tests\TestCase;

class LegalDomainReferenceControllerTest extends TestCase
{
    use HasVisibleFieldAssertions;

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    public function testItRendersTheIndexPage(): void
    {
        $reference = Reference::factory()->create();
        $domains = LegalDomain::factory(3)->create();
        $reference->legalDomains()->attach($domains->modelKeys());
        $routeName = 'collaborate.corpus.references.legal-domains.index';
        $route = route($routeName, ['reference' => $reference->id]);
        $this->validateAuthGuard($route);
        $this->signIn();
        $this->validateCollaborateRole($route);
        $response = $this->get($route)->assertSuccessful();
        $this->assertSeeVisibleFields($domains[0], $response);
        $this->assertSeeVisibleFields($domains[1], $response);
        $this->assertSeeVisibleFields($domains[2], $response);
    }
}
