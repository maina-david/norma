<?php

namespace Tests\Feature\Api\Internals\My\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Requirements\Summary;
use Tests\Feature\My\MyTestCase;

class ReferenceSummaryControllerTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testGettingContent(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        Summary::create(['reference_id' => $reference->id, 'summary_body' => 'testing content']);

        $this->getJson(route('api.my.references.summary.show', ['reference' => $reference->id, 'language' => 'kr']))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'content' => 'testing content',
                ],
            ]);
    }
}
