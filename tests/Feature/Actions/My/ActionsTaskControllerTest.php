<?php

namespace Tests\Feature\Actions\My;

use App\Enums\System\LibryoModule;
use App\Enums\Tasks\Frequency;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Exception;
use Tests\Feature\My\MyTestCase;
use Tests\Feature\Traits\HasDestroyTests;

class ActionsTaskControllerTest extends MyTestCase
{
    use HasDestroyTests;

    public function testListingAndViewing(): void
    {
        /** @var \App\Models\Customer\Libryo $libryo */
        /** @var \App\Models\Customer\Organisation $org */
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $libryo->enableModule(LibryoModule::actions());

        $this->get(route('my.actions.tasks.index', ['view' => 'list']))
            ->assertSuccessful()
            ->assertSee('my-libryo');
        $this->get(route('my.actions.tasks.index', ['view' => 'calendar']))
            ->assertSuccessful()
            ->assertSee('Calendar View');
    }

    public function testViewingDetails(): void
    {
        /** @var \App\Models\Customer\Libryo $libryo */
        /** @var \App\Models\Customer\Organisation $org */
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id]);

        $this->get(route('my.actions.tasks.show', ['task' => $task->hash_id]))
            ->assertSuccessful()
            ->assertSee($task->id);
    }

    public function testDestroy(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->create(['author_id' => $user]);
        $this->destroyAndTestData(Task::class, route('my.actions.tasks.destroy', ['task' => $task]));

        $task = Task::factory()->create();
        $task->update(['author_id' => User::factory()->create()->id]);
        $this->withExceptionHandling()->delete(route('my.actions.tasks.destroy', ['task' => $task]))->assertForbidden();

        // test delete with redirect to filters
        $task = Task::factory()->create();
        $user->updateSetting('app_filters', ['tasks' => ['priority' => 1]]);
        $this->delete(route('my.actions.tasks.destroy', ['task' => $task]))
            ->assertRedirect(route('my.actions.tasks.index', ['view' => 'list', 'priority' => 1]));

        $task = Task::factory()->create();
        $this->delete(route('my.actions.tasks.destroy', ['task' => $task]), ['referer' => route('my.corpus.requirements.index')])
            ->assertRedirect(route('my.corpus.requirements.index'));
    }

    /**
     * @throws Exception
     */
    public function testCreateRecurringTasksDailyFrequency(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = [
            'startDate' => '2025-01-01',
            'frequencyNumber' => 1,
            'frequencyUnit' => Frequency::DAILY->value,
            'endCondition' => 'never',
            'endDate' => null,
            'occurrences' => null,
            'selectedDays' => [],
        ];

        $response = $this->postJson(route('api.my.actions.task.recurrence.store', ['task' => $task->hash_id]), $payload);

        $response->assertSuccessful();
    }

    /**
     * @throws Exception
     */
    public function testCreateRecurringTasksWithWeeklyFrequency(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = [
            'startDate' => '2025-01-01',
            'frequencyNumber' => 1,
            'frequencyUnit' => Frequency::WEEKLY->value,
            'endCondition' => 'never',
            'endDate' => null,
            'occurrences' => null,
            'selectedDays' => ['MO', 'WE', 'FR'],
        ];

        $response = $this->postJson(route('api.my.actions.task.recurrence.store', ['task' => $task->hash_id]), $payload);

        $response->assertSuccessful();
    }

    /**
     * @throws Exception
     */
    public function testCreateRecurringTasksWithMonthlyFrequency(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = [
            'startDate' => '2025-01-01',
            'frequencyNumber' => 1,
            'frequencyUnit' => Frequency::MONTHLY->value,
            'monthSelection' => 'dayOfMonth',
            'monthDay' => 15,
            'weekDayName' => null,
            'endCondition' => 'never',
            'endDate' => null,
            'occurrences' => null,
            'selectedDays' => [],
        ];

        $response = $this->postJson(route('api.my.actions.task.recurrence.store', ['task' => $task->hash_id]), $payload);

        $response->assertSuccessful();
    }

    /**
     * @throws Exception
     */
    public function testCreateRecurringTasksWithMonthWeeklyFrequency(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = [
            'startDate' => '2025-01-01',
            'frequencyNumber' => 1,
            'frequencyUnit' => Frequency::MONTHLY->value,
            'monthSelection' => 'weekOfMonth',
            'monthDay' => null,
            'weekDayName' => 'MO',
            'endCondition' => 'never',
            'endDate' => null,
            'occurrences' => null,
            'selectedDays' => [],
        ];

        $response = $this->postJson(route('api.my.actions.task.recurrence.store', ['task' => $task->hash_id]), $payload);

        $response->assertSuccessful();
    }

    /**
     * @throws Exception
     */
    public function testCreateRecurringTasksWithEndDate(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = [
            'startDate' => '2025-01-01',
            'frequencyNumber' => 1,
            'frequencyUnit' => Frequency::DAILY->value,
            'endCondition' => 'onDate',
            'endDate' => '2025-12-31',
            'occurrences' => null,
            'selectedDays' => [],
        ];

        $response = $this->postJson(route('api.my.actions.task.recurrence.store', ['task' => $task->hash_id]), $payload);

        $response->assertSuccessful();
    }

    /**
     * @throws Exception
     */
    public function testCreateRecurringTasksWithOccurrences(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = [
            'startDate' => '2025-01-01',
            'frequencyNumber' => 1,
            'frequencyUnit' => Frequency::DAILY->value,
            'endCondition' => 'afterOccurrences',
            'endDate' => null,
            'occurrences' => 10,
            'selectedDays' => [],
        ];

        $response = $this->postJson(route('api.my.actions.task.recurrence.store', ['task' => $task->hash_id]), $payload);

        $response->assertSuccessful();
    }

    /**
     * @throws Exception
     */
    public function testClearRecurrence(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::actions());

        $task = Task::factory()->create(['place_id' => $libryo->id, 'assigned_to_id' => $user->id]);

        $payload = [
            'startDate' => '2025-01-01',
            'frequencyNumber' => 1,
            'frequencyUnit' => Frequency::DAILY->value,
            'endCondition' => 'afterOccurrences',
            'endDate' => null,
            'occurrences' => 10,
            'selectedDays' => [],
        ];

        $response = $this->postJson(route('api.my.actions.task.recurrence.store', ['task' => $task->hash_id]), $payload);

        $response->assertSuccessful();

        $response2 = $this->postJson(route('api.my.actions.task.recurrence.clear', ['task' => $task->hash_id]));

        $response2->assertSuccessful();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'rrule' => null,
        ]);
    }
}
