<?php

namespace Tests\Feature\Api\Internals\My\Actions;

use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class ActionAreaListingControllerTest extends MyTestCase
{
    public function testListingActions(): void
    {
        /** @var \App\Models\Customer\Norma $norma */
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->load(['work']);
        $norma->references()->attach($reference->id);
        $control = Category::factory()->create(['parent_id' => Category::factory(), 'level' => 2]);
        $subject = Category::factory()->create(['level' => 1]);
        $action = ActionArea::factory()->create(['control_category_id' => $control->id, 'subject_category_id' => $subject->id]);
        $action->references()->attach($reference->id);

        $this->getJson(route('api.my.actions.planner.areas.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $action->id],
                ],
            ]);

        Storage::fake();

        $response = $this->getJson(route('api.my.actions.planner.export', ['group' => 'controls', 'type' => 'excel']))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'target',
                    'job',
                ],
            ]);

        $this->assertNotNull($response->json('data.target'));
        $filename = Str::afterLast($response->json('data.target'), '/');
        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . urldecode($filename);
        Storage::assertExists($path);
        Storage::delete($path);

        $this->activateAllStreams($user);

        $response = $this->getJson(route('api.my.actions.planner.export', ['group' => 'controls', 'type' => 'excel']))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'target',
                    'job',
                ],
            ]);

        $this->assertNotNull($response->json('data.target'));
        $filename = Str::afterLast($response->json('data.target'), '/');
        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . urldecode($filename);
        Storage::assertExists($path);
        Storage::delete($path);
    }

    public function testListingReferences(): void
    {
        /** @var \App\Models\Customer\Norma $norma */
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $reference = Reference::factory()->create();
        $reference->load(['work']);
        $norma->references()->attach($reference->id);
        $action = ActionArea::factory()->create();
        $action->references()->attach($reference->id);

        $this->getJson(route('api.my.actions.planner.references.index'))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $reference->id],
                ],
            ]);
    }
}
