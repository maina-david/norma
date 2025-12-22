<?php

namespace Tests\Feature\Ontology\My;

use Tests\Feature\My\MyTestCase;

class TagStreamControllerTest extends MyTestCase
{
    public function testSearchSuggest(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($norma);

        $routeName = 'my.ontology.tags.search-suggest';
        // test without a search query
        $this->get(route($routeName, ['key' => 'reference-suggest']))
            ->assertSuccessful()
            ->assertDontSee($tag->title);

        $this->activateAllStreams($user);

        // can't test fulltext search with database transactions, so just test success
        $this->get(route($routeName, ['key' => 'reference-suggest', 'search' => substr($tag->title, 0, 3), 'link' => 1]))
            ->assertSuccessful();

        $this->deleteCompiledStream();
    }
}
