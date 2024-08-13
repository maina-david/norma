<?php

namespace Tests\Feature\Api\Internals\My\Actions;

use App\Enums\Ontology\CategoryType;
use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Http\ResourceActions\Tasks\ChangeAssignee;
use App\Http\ResourceActions\Tasks\ChangeImpact;
use App\Http\ResourceActions\Tasks\ChangePriority;
use App\Http\ResourceActions\Tasks\ChangeStatus;
use App\Models\Actions\ActionArea;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Ontology\Category;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class TaskControllerTest extends MyTestCase
{
    public function testListingAndUpdatingTasks(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $this->getJson(route('api.my.actions.tasks.index', ['sort' => 'title']))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    ['id' => $task->id],
                ],
            ]);

        $newTitle = $this->faker->sentence();

        $reference = Reference::factory()->create();

        $filters = [
            'actionArea' => 1,
            'controlTypes' => [1],
            'topics' => [1],
        ];

        $this->getJson(route('api.my.actions.tasks.index', ['sort' => 'title', ...$filters]))
            ->assertSuccessful();

        $payload = [
            'title' => $this->faker->sentence(),
            'task_status' => TaskStatus::notStarted()->value,
            'taskable_type' => $reference->getMorphClass(),
            'taskable_id' => $reference->id,
        ];

        $this->postJson(route('api.my.actions.tasks.store'), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'title' => $payload['title'],
                ],
            ]);

        $this->activateAllStreams($user);

        $payload['title'] = $this->faker->sentence();
        $payload['libryo_id'] = $libryo->id;
        $payload['copy'] = true;

        $this->postJson(route('api.my.actions.tasks.store'), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'title' => $payload['title'],
                ],
            ]);

        app(ActiveLibryosManager::class)->activate($user, $libryo);

        $context = ContextQuestion::factory()->create();

        $payload = [
            'title' => $this->faker->sentence(),
            'task_status' => TaskStatus::notStarted()->value,
            'taskable_type' => $context->getMorphClass(),
            'taskable_id' => $context->id,
        ];

        $this->postJson(route('api.my.actions.tasks.store'), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'title' => $payload['title'],
                ],
            ]);

        $this->putJson(route('api.my.actions.tasks.update', ['task' => $task->hash_id]), ['title' => $newTitle])
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => $newTitle,
                ],
            ]);

        $this->assertDatabaseHas(Task::class, ['id' => $task->id, 'title' => $newTitle]);
    }

    public function testActions(): void
    {
        /** @var \App\Models\Customer\Libryo $libryo */
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $reference = Reference::factory()->create();
        $reference->load(['work', 'refPlainText']);
        $libryo->references()->attach($reference->id);
        $action = ActionArea::factory()->create();
        $action->references()->attach($reference->id);

        $task = Task::factory()->create([
            'taskable_id' => $reference->id,
            'taskable_type' => $reference->getMorphClass(),
            'place_id' => $libryo->id,
            'task_status' => TaskStatus::inProgress()->value,
            'action_area_id' => $action->id,
            'assigned_to_id' => null,
            'impact' => 1,
            'priority' => TaskPriority::low()->value,
        ]);

        (new ChangeAssignee())->label();
        (new ChangeImpact())->label();
        (new ChangePriority())->label();
        (new ChangeStatus())->label();

        $payload = ['selected' => [$task->id], (new ChangeAssignee())->actionId() => $user->hash_id];

        $this->postJson(route('api.my.actions.tasks.action.trigger', ['action' => (new ChangeAssignee())->actionId()]), $payload)
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertSame($user->id, $task->refresh()->assigned_to_id);

        $payload = ['selected' => [$task->id], (new ChangeImpact())->actionId() => 5];

        $this->postJson(route('api.my.actions.tasks.action.trigger', ['action' => (new ChangeImpact())->actionId()]), $payload)
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertSame(5, $task->refresh()->impact);

        $payload = ['selected' => [$task->id], (new ChangePriority())->actionId() => TaskPriority::high()->value];

        $this->postJson(route('api.my.actions.tasks.action.trigger', ['action' => (new ChangePriority())->actionId()]), $payload)
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertSame(TaskPriority::high()->value, $task->refresh()->priority);

        $payload = ['selected' => [$task->id], (new ChangeStatus())->actionId() => TaskStatus::paused()->value];

        $this->postJson(route('api.my.actions.tasks.action.trigger', ['action' => (new ChangeStatus())->actionId()]), $payload)
            ->assertSuccessful()
            ->assertExactJson([]);

        $this->assertSame(TaskStatus::paused()->value, $task->refresh()->priority);
    }

    public function testFetchingGroups(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $otherUser = User::factory()->create();
        $otherUser->libryos()->attach($libryo->id);
        $otherUser->organisations()->attach($org->id);

        $taskOne = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);
        $taskTwo = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $otherUser->id]);

        $options = User::whereKey([$user->id, $otherUser->id])
            ->orderBy('fname')
            ->get(['fname', 'sname', 'id'])
            ->map(fn ($user) => [
                'label' => $user->full_name,
                'value' => $user->id,
            ]);

        $options->push([
            'label' => 'Other',
            'value' => null,
        ]);

        $this->getJson(route('api.my.actions.tasks.groups.index', ['field' => 'assignee']))
            ->assertSuccessful()
            ->assertExactJson(['data' => $options->all()]);

        \App\Models\Ontology\CategoryType::factory()->create(['id' => CategoryType::CONTROL->value]);
        \App\Models\Ontology\CategoryType::factory()->create(['id' => CategoryType::SUBJECT->value]);

        $actionOne = ActionArea::factory()->create();
        $actionTwo = ActionArea::factory()->create();
        $options = Category::whereKey([$actionOne->control_category_id, $actionTwo->control_category_id])
            ->orderBy('display_label')
            ->get(['display_label', 'id'])
            ->map(fn ($item) => [
                'label' => $item->display_label,
                'value' => $item->id,
            ]);

        $options->push([
            'label' => 'Other',
            'value' => null,
        ]);

        $taskOne->update(['action_area_id' => $actionOne->id]);
        $taskTwo->update(['action_area_id' => $actionTwo->id]);

        $this->getJson(route('api.my.actions.tasks.groups.index', ['field' => 'control']))
            ->assertSuccessful()
            ->assertExactJson(['data' => $options]);

        $options = Category::whereKey([$actionOne->subject_category_id, $actionTwo->subject_category_id])
            ->orderBy('display_label')
            ->get(['display_label', 'id'])
            ->map(fn ($item) => [
                'label' => $item->display_label,
                'value' => $item->id,
            ]);

        $options->push([
            'label' => 'Other',
            'value' => null,
        ]);

        $this->getJson(route('api.my.actions.tasks.groups.index', ['field' => 'topic']))
            ->assertSuccessful()
            ->assertExactJson(['data' => $options]);
    }

    public function testGeneratingExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        Storage::fake();

        Task::factory(5)->for($libryo)->create();

        $response = $this->getJson(route('api.my.actions.tasks.export', ['type' => 'excel']))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'job',
                    'target',
                ],
            ]);
        $redirectUrl = $response->json('data.target');
        $filename = urldecode(Str::afterLast($redirectUrl, '/'));

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::assertExists($path);
        Storage::delete($path);

        $this->activateAllStreams($user);
        $this->getJson(route('api.my.actions.tasks.export', ['type' => 'excel']))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'job',
                    'target',
                ],
            ]);

        $this->withExceptionHandling()->getJson(route('api.my.actions.tasks.export', ['type' => 'pdf']))->assertNotFound();
        $this->withExceptionHandling()->getJson(route('api.my.actions.tasks.export', ['type' => 'fake']))->assertNotFound();
    }

    public function testCopyTask(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = ['source_task_id' => $task->id];

        $this->postJson(route('api.my.actions.tasks.copy', $task->id), $payload)
            ->assertSuccessful();

        $task->update(['source_task_id' => $task->id]);

        $this->assertDatabaseHas(Task::class, ['source_task_id' => $task->id]);
    }
}
