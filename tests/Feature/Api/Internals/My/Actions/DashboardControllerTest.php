<?php

namespace Tests\Feature\Api\Internals\My\Actions;

use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class DashboardControllerTest extends MyTestCase
{
    public function testListingAndExporting(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $action = ActionArea::factory()->create();
        $ref = Reference::factory()->create();
        $ref->actionAreas()->attach($action->id);
        $task = Task::factory()->create(['place_id' => $libryo->id, 'taskable_type' => $ref->getMorphClass(), 'taskable_id' => $ref->id]);

        $this->getJson(route('api.my.actions.tasks.dashboard.index'))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $libryo->id,
                        'title' => $libryo->title,
                        'total_tasks' => 1,
                        'total_in_progress_tasks' => 0,
                        'total_not_started_tasks' => 1,
                        'overdue_tasks' => 0,
                        'completed_total_impact' => 0,
                        'incomplete_total_impact' => null,
                    ],
                ],
            ]);

        Storage::fake();

        $this->activateAllStreams($user);

        $response = $this->getJson(route('api.my.actions.tasks.dashboard.export', ['type' => 'excel']))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'target',
                    'job',
                ],
            ]);

        $this->assertNotNull($response->json('data.target'));

        $filename = Str::afterLast($response->json('data.target'), '/');

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::assertExists($path);
    }
}
