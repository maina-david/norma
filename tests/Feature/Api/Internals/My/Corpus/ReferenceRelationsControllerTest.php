<?php

namespace Tests\Feature\Api\Internals\My\Corpus;

use App\Enums\Corpus\ReferenceLinkType;
use App\Models\Corpus\Reference;
use Tests\Feature\My\MyTestCase;

class ReferenceRelationsControllerTest extends MyTestCase
{
    public function testFetchingLinkedReferences(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->normas()->attach($norma->id);

        $consequence = Reference::factory()->create();

        $reference->linkedChildren()->attach($consequence->id, [
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $consequence->work_id,
            'link_type' => ReferenceLinkType::CONSEQUENCE->value,
        ]);

        $this->getJson(route('api.my.references.read-withs.relation.index', ['reference' => $reference->id, 'relation' => 'consequences']))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    ['id' => $consequence->id],
                ],
            ]);
    }
}
