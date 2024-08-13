<?php

namespace Tests\Feature\Api\Internals\My\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use Tests\Feature\My\MyTestCase;

class ReferenceContentControllerTest extends MyTestCase
{
    /**
     * @return void
     */
    public function testGettingContent(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $reference = Reference::factory()->create();
        ReferenceContent::create(['reference_id' => $reference->id, 'cached_content' => 'testing content']);

        $this->getJson(route('api.my.references.content.show', ['reference' => $reference->id, 'language' => 'kr']))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'content' => 'testing content',
                ],
            ]);
    }
}
