<?php

namespace Tests\Feature\Api\Internals\My\Corpus;

use App\Enums\Corpus\ReferenceLinkType;
use App\Models\Corpus\Reference;
use Tests\Feature\My\MyTestCase;

class ReferenceReadWithControllerTest extends MyTestCase
{
    public function testGettingCountsAndDetails(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->normas()->attach($norma->id);

        $read = Reference::factory()->create();
        $amend = Reference::factory()->create();
        $amend2 = Reference::factory()->create();
        $consequence = Reference::factory()->create();

        $reference->linkedChildren()->attach($consequence->id, [
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $consequence->work_id,
            'link_type' => ReferenceLinkType::CONSEQUENCE->value,
        ]);
        $reference->linkedChildren()->attach($read->id, [
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $read->work_id,
            'link_type' => ReferenceLinkType::READ_WITH->value,
        ]);
        $reference->linkedChildren()->attach($amend->id, [
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $amend->work_id,
            'link_type' => ReferenceLinkType::AMENDMENT->value,
        ]);
        $reference->linkedChildren()->attach($amend2->id, [
            'parent_work_id' => $reference->work_id,
            'child_work_id' => $amend2->work_id,
            'link_type' => ReferenceLinkType::AMENDMENT->value,
        ]);

        $this->getJson(route('api.my.references.read-withs.index', ['reference' => $reference]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'amendments' => 2,
                    'read_with' => 1,
                    'consequences' => 1,
                ],
            ]);
    }
}
