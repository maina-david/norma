<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Models\Corpus\CatalogueDoc;
use Tests\TestCase;

class CatalogueDocControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testGettingDocs(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $doc = CatalogueDoc::factory()->create();

        $this->getJson(route('api.collaborate.catalogue-docs.index', ['search' => $doc->title]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $doc->id,
                    ],
                ],
            ]);
    }
}
