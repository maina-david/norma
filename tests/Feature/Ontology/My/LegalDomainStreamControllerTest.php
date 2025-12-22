<?php

namespace Tests\Feature\Ontology\My;

use Tests\Feature\My\MyTestCase;

class LegalDomainStreamControllerTest extends MyTestCase
{
    public function testSearchSuggest(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($norma);

        $routeName = 'my.ontology.legal-domains.search-suggest';
        // test without a search query
        $this->get(route($routeName, ['key' => 'reference-suggest']))
            ->assertSuccessful()
            ->assertDontSee($domain->title);

        $this->activateAllStreams($user);

        $this->get(route($routeName, ['key' => 'reference-suggest', 'search' => substr($domain->title, 0, 3), 'link' => 1]))
            ->assertSuccessful()
            ->assertSee($domain->title);

        $this->deleteCompiledStream();
    }
}
