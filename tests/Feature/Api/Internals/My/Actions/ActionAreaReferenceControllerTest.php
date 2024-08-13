<?php

namespace Tests\Feature\Api\Internals\My\Actions;

use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Tests\Feature\My\MyTestCase;

class ActionAreaReferenceControllerTest extends MyTestCase
{
    public function testIndexListing(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $action = ActionArea::factory()->create();
        $work = Work::factory()->create();
        $references = Reference::factory()->count(3)->create(['work_id' => $work->id]);
        $action->references()->attach($references->pluck('id'));
        $libryo->references()->sync($references->pluck('id'));

        $this->getJson(route('api.my.actions.references.index', ['action' => $action->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => $references->map(fn ($reference) => [
                    'id' => $reference->id,
                    'title' => $reference->refPlainText->plain_text,
                    'work_title' => $reference->work->title,
                    'flag' => $reference->work->primaryLocation->flag,
                ])->all(),
            ]);
    }
}
