<?php

namespace Tests\Feature\Api\Internals\My\Corpus;

use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\ReferenceContentExtract;
use App\Models\Tasks\Task;
use Config;
use Tests\Feature\My\MyTestCase;
use Tests\Traits\UsesLibryoAI;

class ReferenceExtractControllerTest extends MyTestCase
{
    use UsesLibryoAI;

    public function testGettingExtracts(): void
    {
        Config::set('services.libryo_ai.enabled', true);
        $expected = $this->getExpectations();
        $this->mockLibryoAIGenerateTaskRequest();

        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $action = ActionArea::factory()->create();
        $reference = Reference::factory()->create();

        ReferenceContent::factory()->create(['reference_id' => $reference->id]);

        $this->getJson(route('api.my.references.extracts.index', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertJsonCount(count($expected), 'data');

        $extracts = ReferenceContentExtract::where('reference_id', $reference->id)->get();
        Task::factory()->create(['place_id' => $libryo->id, 'action_area_id' => $action->id, 'reference_content_extract_id' => $extracts[0]->id]);

        $this->getJson(route('api.my.references.extracts.index', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => $extracts->map(fn ($item, $index) => [
                    'id' => $item->id,
                    'content' => $item->content,
                    'attached' => $index === 0,
                ])->all(),
            ]);
    }
}
