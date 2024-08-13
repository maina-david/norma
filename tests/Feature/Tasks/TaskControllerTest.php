<?php

namespace Tests\Feature\Tasks;

use App\Enums\Tasks\TaskActivityType;
use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Reminder;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskActivity;
use App\Models\Tasks\TaskProject;
use App\Services\Customer\ActiveLibryosManager;
use App\Stores\Notify\ReminderStore;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;
use Tests\Feature\Tasks\Traits\RunsShowTests;
use Tests\Feature\Traits\HasDestroyTests;
use Tests\Feature\Traits\TestsVisibleFormLabels;

class TaskControllerTest extends MyTestCase
{
    use HasDestroyTests;
    use TestsVisibleFormLabels;
    use RunsShowTests;

    private function createTasksOfTypes(Libryo $libryo): array
    {
        $task2 = Task::factory()->for($libryo)->create(['task_status' => TaskStatus::inProgress()->value, 'taskable_type' => 'file', 'taskable_id' => File::factory()->create()->id]);
        $task3 = Task::factory()->for($libryo)->create(['task_status' => TaskStatus::inProgress()->value, 'taskable_type' => 'assessment_item_response', 'taskable_id' => AssessmentItemResponse::factory()->create()->id]);
        $task4 = Task::factory()->for($libryo)->create(['task_status' => TaskStatus::paused()->value, 'taskable_type' => 'register_notification', 'taskable_id' => LegalUpdate::factory()->create()->id]);
        $task5 = Task::factory()->for($libryo)->create(['task_status' => TaskStatus::notStarted()->value, 'taskable_type' => 'register_item', 'taskable_id' => Reference::factory()->create()->id]);
        $task5 = Task::factory()->for($libryo)->create(['task_status' => TaskStatus::notStarted()->value, 'taskable_type' => 'context_question', 'taskable_id' => ContextQuestion::factory()->create()->id]);

        return [$task2, $task3, $task4, $task5];
    }

    public function testIndex(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.tasks.tasks.index';

        $task = Task::factory()->for($libryo)->create(['author_id' => $user->id, 'assigned_to_id' => $user->id, 'due_on' => now()->addDays(2)]);
        // for testing the TaskType Component
        [$task2, $task3, $task4, $task5] = $this->createTasksOfTypes($libryo);

        $this->signIn($user);

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
        $response->assertSeeSelector('//div[text()[contains(.,"Not Started")]]');

        $response = $this->get(route($routeName, ['statuses' => [TaskStatus::inProgress()->value]]))->assertSuccessful();
        $response->assertDontSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
        $response = $this->get(route($routeName, ['statuses' => [TaskStatus::notStarted()->value]]))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        app(ActiveLibryosManager::class)->activate($user, $libryo);
        $response = $this->get(route($routeName, ['priority' => TaskPriority::low()->value]))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
        $response = $this->get(route($routeName, ['priority' => TaskPriority::high()->value]))->assertSuccessful();
        $response->assertDontSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $response = $this->get(route($routeName, ['type' => 'generic']))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
        $response = $this->get(route($routeName, ['type' => 'updates']))->assertSuccessful();
        $response->assertDontSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
        $response = $this->get(route($routeName, ['type' => 'nonsense']))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $response = $this->get(route($routeName, ['search' => Str::random(40)]))->assertSuccessful();
        $response->assertDontSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
        $response = $this->get(route($routeName, ['search' => substr($task->title, 0, 5)]))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $this->get(route($routeName, ['overdue' => 'true']))->assertSuccessful();
        $response = $this->get(route($routeName, ['archived' => 'true']))->assertSuccessful();
        $response->assertDontSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
        $response = $this->get(route($routeName, ['archived' => 'false']))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $response = $this->get(route($routeName, ['assignee' => $user->hash_id]))->assertSuccessful();
        $response->assertSeeSelector('//td//img[@data-tippy-content[contains(.,"' . $task->assignee->fullName . '")]]');
        $response = $this->get(route($routeName, ['assignee' => User::factory()->create()->id]))->assertSuccessful();
        $response->assertDontSeeSelector('//td//img[@data-tippy-content[contains(.,"' . $task->assignee->fullName . '")]]');

        $project = TaskProject::factory()->create();
        $task = Task::factory()->for($libryo)->create(['task_project_id' => $project->id, 'assigned_to_id' => $user->id]);
        $response = $this->get(route($routeName, ['project' => $project->id]))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $task = Task::factory()->for($libryo)->create(['impact' => 1, 'assigned_to_id' => $user->id]);
        $response = $this->get(route($routeName, ['min_impact' => 1, 'max_impact' => 1]))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $task = Task::factory()->for($libryo)->create(['due_on' => now()->addMonth(), 'assigned_to_id' => $user->id]);
        $this->get(route($routeName, ['start_date' => now()->addMonths(2)->toDateString(), 'end_date' => now()->addMonths(3)->toDateString()]))
            ->assertSuccessful()
            ->assertDontSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $this->get(route($routeName, ['start_date' => now()->subMonths(2)->toDateString(), 'end_date' => now()->addMonths(3)->toDateString()]))
            ->assertSuccessful()
            ->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $this->get(route($routeName, ['streams' => [$libryo->id], 'start_date' => now()->subMonths(2)->toDateString(), 'end_date' => now()->addMonths(3)->toDateString()]))
            ->assertSuccessful()
            ->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $this->get(route('my.tasks.tasks.calendar.index'))
            ->assertSuccessful()
            ->assertSeeSelector('//div[text()[contains(.,"' . now()->format('F Y') . '")]]')
            ->assertDontSeeSelector('//div[text()[contains(.,"' . $task->title . '")]]');

        $nextMonth = now()->addMonth();

        $this->get(route('my.tasks.tasks.calendar.index', ['show-month' => $nextMonth->month, 'show-year' => $nextMonth->year]))
            ->assertSuccessful()
            ->assertSeeSelector('//div[text()[contains(.,"' . $nextMonth->format('F Y') . '")]]')
            ->assertSeeSelector('//a[text()[contains(.,"' . $task->title . '")]]');

        app(ActiveLibryosManager::class)->activateAll($user);
        $response = $this->followingRedirects()->get(route($routeName))->assertSuccessful();
    }

    public function testIndexSavedFilters(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.tasks.tasks.index';

        $task = Task::factory()->for($libryo)->create(['author_id' => $user->id, 'assigned_to_id' => $user->id]);

        $this->signIn($user);
        $user->updateSetting('app_filters', ['tasks' => ['type' => 'updates']]);

        $response = $this->get(route($routeName))->assertRedirect();
        $response->assertDontSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');

        $user->updateSetting('app_filters', ['tasks' => []]);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSeeSelector('//a/span[text()[contains(.,"' . $task->title . '")]]');
    }

    public function testDestroy(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.tasks.tasks.destroy';
        $task = Task::factory()->create(['author_id' => $user]);
        $this->destroyAndTestData(Task::class, route($routeName, ['task' => $task]));

        $task = Task::factory()->create();
        $task->update(['author_id' => User::factory()->create()->id]);
        $this->withExceptionHandling()->delete(route($routeName, ['task' => $task]))->assertForbidden();

        // test delete with redirect to filters
        $task = Task::factory()->create();
        $user->updateSetting('app_filters', ['tasks' => ['priority' => 1]]);
        $this->delete(route($routeName, ['task' => $task]))
            ->assertRedirect(route('my.tasks.tasks.index', ['priority' => 1]));

        $task = Task::factory()->create();
        $this->delete(route($routeName, ['task' => $task]), ['referer' => route('my.corpus.requirements.index')])
            ->assertRedirect(route('my.corpus.requirements.index'));
    }

    public function testShow(): void
    {
        $routeName = 'my.tasks.tasks.show';
        [$user, $libryo, $org] = $this->runShowTest($routeName);
        $task = Task::factory()->for($libryo)->create(['author_id' => $user->id]);

        $response = $this->get(route($routeName, ['task' => $task->hash_id]))->assertSuccessful();

        [$task2, $task3, $task4, $task5] = $this->createTasksOfTypes($libryo);
        $this->get(route($routeName, ['task' => $task2->hash_id]))->assertSuccessful();
        $this->get(route($routeName, ['task' => $task3->hash_id]))->assertSuccessful();
        $this->get(route($routeName, ['task' => $task4->hash_id]))->assertSuccessful();
        $this->get(route($routeName, ['task' => $task5->hash_id]))->assertSuccessful();
    }

    public function testCreate(): void
    {
        $routeName = 'my.tasks.tasks.create';
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $project = TaskProject::factory()->for($org)->create();

        $response = $this->assertSeeVisibleFormLabels(
            [],
            route($routeName),
            ['Title', 'Description', 'Status', 'Due On', 'Priority', 'Assigned To', 'Followers', 'Project', 'Who should receive this reminder']
        );

        app(ActiveLibryosManager::class)->activateAll($user);
        $response = $this->get(route($routeName))->assertRedirect();
    }

    public function testStore(): void
    {
        $routeName = 'my.tasks.tasks.store';
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $otherLibryo = Libryo::factory()->for($org)->create();
        $otherLibryo->users()->attach($user->id);
        $nonRelatedLibryo = Libryo::factory()->create();
        $reference = Reference::factory()->create();
        $reference->libryos()->attach([$libryo->id, $otherLibryo->id, $nonRelatedLibryo->id]);
        $task = Task::factory()->for($libryo)->make([
            'taskable_id' => $reference->id,
            'taskable_type' => $reference->getMorphClass(),
        ]);
        $user2 = User::factory()->create();
        $user2->organisations()->attach($org);

        $data = $task->toArray();
        $data['followers'] = [$user2->id];
        $data['copy'] = 1;

        $count = Task::count();
        $response = $this->post(route($routeName), $data);
        $response->assertSessionDoesntHaveErrors();
        $this->assertGreaterThan($count, Task::count());

        $this->assertTrue(Task::whereNotNull('source_task_id')->exists());
        $this->assertTrue(Task::where('place_id', $libryo->id)->exists());
        $this->assertTrue(Task::where('place_id', $otherLibryo->id)->exists());
        $this->assertFalse(Task::where('place_id', $nonRelatedLibryo->id)->exists());

        $newTask = Task::with(['watchers'])->where('place_id', $libryo->id)->get()->last();
        $this->assertTrue($newTask->watchers->contains($user2));
        $this->assertFalse($newTask->watchers->contains($user));

        app(ActiveLibryosManager::class)->activateAll($user);
        $response = $this->post(route($routeName), $data)->assertRedirect();

        $data['libryo_id'] = $libryo->id;
        $data['save_and_back'] = route('my.corpus.requirements.index');

        $this->post(route($routeName), $data)
            ->assertRedirect(route('my.corpus.requirements.index'));

        $data['libryo_id'] = $libryo->id;
        $data['save_and_back'] = route('my.corpus.requirements.index');

        $data['title'] = 'Saving using target';
        $data['target_libryo_id'] = $otherLibryo->id;

        $this->assertDatabaseMissing(Task::class, ['title' => $data['title']]);

        $this->post(route($routeName), $data)
            ->assertRedirect(route('my.corpus.requirements.index'));

        $this->assertDatabaseHas(Task::class, ['title' => $data['title']]);
    }

    public function testStoreWithReminder(): void
    {
        $routeName = 'my.tasks.tasks.store';
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->for($libryo)->make(['taskable_id' => $libryo->id]);

        $count = Task::count();
        $remindersCount = Reminder::count();
        $data = $task->toArray();
        $data['reminder'] = true;
        $data['remind_on_date'] = Carbon::now()->addDays(3)->format('Y-m-d');
        $data['remind_on_time'] = '12:35';
        $response = $this->post(route($routeName), $data);
        $response->assertSessionDoesntHaveErrors();
        $this->assertGreaterThan($count, Task::count());
        $this->assertGreaterThan($remindersCount, Reminder::count());

        $reminder = Reminder::all()->last();
        $this->assertSame($user->id, $reminder->author_id);
        $this->assertSame($task->title, $reminder->title);
        $this->assertNull($reminder->place_id);
        $this->assertNull($reminder->organisation_id);
        $checkDate = Carbon::parse(Carbon::now()->addDays(3)->format('Y-m-d') . ' 12:35:00', $user->timezone)->setTimezone('UTC');
        $this->assertSame($checkDate->format('Y-m-d H:i:s'), $reminder->remind_on->format('Y-m-d H:i:s'));

        $data['notification_config'] = [1, 2];
        unset($data['remind_on_time'], $data['remind_on_date']);
        $response = $this->post(route($routeName), $data);
        $reminder = Reminder::all()->last();
        $this->assertSame($data['notification_config'], $reminder->notification_config);
        // also check default values for reminders
        $checkDate = Carbon::parse(Carbon::now()->addDays(3)->format('Y-m-d') . ' ' . ReminderStore::DEFAULT_TIME, $user->timezone)->setTimezone('UTC');
        $this->assertSame($checkDate->format('Y-m-d H:i:s'), $reminder->remind_on->format('Y-m-d H:i:s'));

        // check that the task due date is used as default
        $data['due_on'] = Carbon::now()->addDays(2)->format('Y-m-d');
        $response = $this->post(route($routeName), $data);
        $checkDate = Carbon::parse(Carbon::now()->addDays(2)->format('Y-m-d') . ' ' . ReminderStore::DEFAULT_TIME, $user->timezone)->setTimezone('UTC');
        $reminder = Reminder::all()->last();
        $this->assertSame($checkDate->format('Y-m-d H:i:s'), $reminder->remind_on->format('Y-m-d H:i:s'));
    }

    public function testEdit(): void
    {
        $routeName = 'my.tasks.tasks.edit';
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->for($libryo)->create(['author_id' => $user->id]);

        $response = $this->assertSeeVisibleFormLabels(
            $task,
            route($routeName, ['task' => $task->hash_id]),
            ['Title', 'Description', 'Status', 'Due On', 'Priority', 'Assigned To', 'Followers', 'Project'],
            ['title'],
        );

        $response->assertDontSeeSelector('//label[text()[contains(.,"Send Reminder")]]');
    }

    public function testUpdate(): void
    {
        $routeName = 'my.tasks.tasks.update';
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->for($libryo)->create();

        $task->title = 'New Title';
        $task->due_on = now()->addDays(2);
        $task->task_status = TaskStatus::inProgress()->value;
        $task->priority = TaskPriority::veryHigh()->value;
        $task->assigned_to_id = User::factory()->create()->id;

        $task->watchers()->attach($user);

        $user2 = User::factory()->create();
        $user2->organisations()->attach($org);
        $data = $task->toArray();
        $data['followers'] = [$user2->id];
        // just to test unsetting of reminder
        $data['reminder'] = true;
        TaskActivity::all()->each(fn ($a) => $a->delete());
        $taskActivityCount = TaskActivity::count();
        $response = $this->followingRedirects()->put(route($routeName, ['task' => $task->id]), $data)->assertSuccessful();
        $task->refresh();
        $this->assertTrue($task->title === 'New Title');
        $this->assertTrue($task->watchers->contains($user2));
        $this->assertFalse($task->watchers->contains($user));

        // updating the status should trigger a task activity to be tracked
        $this->assertGreaterThan($taskActivityCount, TaskActivity::count());
        $activities = TaskActivity::all();
        // new Watch task activities should have been created
        $this->assertTrue($activities->filter(fn ($a) => $a->activity_type === TaskActivityType::watchTask()->value)->count() > 0);
        // new change status activity
        $this->assertTrue($activities->filter(fn ($a) => $a->activity_type === TaskActivityType::statusChange()->value)->count() > 0);
    }

    public function testExportAsExcel(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.tasks.export.excel';
        Storage::fake();

        Task::factory(5)->for($libryo)->create();

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('redirect=');

        preg_match('/redirect=(.*)\"/', $response->getContent(), $matches);
        $redirectUrl = urldecode($matches[1]);
        $filename = urldecode(Str::afterLast($redirectUrl, '/'));

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::assertExists($path);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
    }
}
