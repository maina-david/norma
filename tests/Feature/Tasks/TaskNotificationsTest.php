<?php

namespace Tests\Feature\Tasks;

use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Tasks\Task;
use App\Notifications\Tasks\TaskAssignedNotification;
use App\Notifications\Tasks\TaskAssigneeChangedNotification;
use App\Notifications\Tasks\TaskCompletedNotification;
use App\Notifications\Tasks\TaskDueDateChangedNotification;
use App\Notifications\Tasks\TaskPriorityChangedNotification;
use App\Notifications\Tasks\TaskStatusChangedNotification;
use App\Notifications\Tasks\TaskTitleChangedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\My\MyTestCase;

class TaskNotificationsTest extends MyTestCase
{
    private function setupUpdateTask(): array
    {
        Notification::fake();
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->for($libryo)->create();

        return [$user, $task, $libryo];
    }

    public function testTitleChangedNotificationNotSentToChanger(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';
        $newTitle = 'New Title';
        $response = $this->put(route($routeName, ['task' => $task->id]), ['title' => $newTitle])->assertSessionDoesntHaveErrors();
        // notificaction should not be sent if the user initiated the action
        Notification::assertNotSentTo(
            $user,
            TaskTitleChangedNotification::class
        );
    }

    public function testTitleChangedAssigneeWatcherNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $watcherUser = User::factory()->create();
        Task::withoutEvents(function () use ($task, $user, $user2) {
            $task->update(['author_id' => $user->id]);
            $task->update(['assigned_to_id' => $user2->id]);
        });
        $task->watchers()->attach($watcherUser);

        // test assignee and watchers get notified
        $newTitle = 'New Title 3';
        $response = $this->put(route($routeName, ['task' => $task->id]), ['title' => $newTitle])->assertSessionDoesntHaveErrors();
        Notification::assertSentTo([$user2, $watcherUser], TaskTitleChangedNotification::class);
        Notification::assertNotSentTo([$user, $user3], TaskTitleChangedNotification::class);

        $notification = new TaskTitleChangedNotification($task, $user->id, $task->title, $newTitle, '');
        $notification->toMail($user);
        $data = $notification->toDatabase($user);
    }

    public function testTitleChangedAuthorNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        $user2 = User::factory()->create();
        // test sending to author when someone else makes the change
        $newTitle = 'New Title 4';
        $task = Task::factory()->for($libryo)->create();
        Task::withoutEvents(function () use ($user2, $task, $user) {
            $task->update(['author_id' => $user2->id, 'assigned_to_id' => $user->id]);
        });
        $response = $this->put(route($routeName, ['task' => $task->id]), ['title' => $newTitle])->assertSessionDoesntHaveErrors();
        Notification::assertSentTo([$user2], TaskTitleChangedNotification::class);
    }

    public function testTitleChangedDisabledSettingNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        $user2 = User::factory()->create();

        // test setting is disabled
        $user2->updateSetting('email_task_title_changed_author', false);
        $newTitle = 'New Title 5';
        $task = Task::factory()->for($libryo)->create();
        Task::withoutEvents(function () use ($user2, $task, $user) {
            $task->update(['author_id' => $user2->id, 'assigned_to_id' => $user->id]);
        });
        $response = $this->put(route($routeName, ['task' => $task->id]), ['title' => $newTitle])->assertSessionDoesntHaveErrors();
        Notification::assertNotSentTo($user2, TaskTitleChangedNotification::class);
    }

    public function testStatusChangeNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $watcherUser = User::factory()->create();
        $watcherUser->updateSetting('email_task_status_changed_watcher', true);
        Task::withoutEvents(function () use ($task, $user, $user2) {
            $task->update(['author_id' => $user->id]);
            $task->update(['assigned_to_id' => $user2->id]);
        });
        $task->watchers()->attach($watcherUser);

        // test assignee and watchers get notified
        // in this case the default setting for email_task_status_changed_assignee is false, so the assignee should not get it
        $newStatus = TaskStatus::inProgress()->value;
        $response = $this->put(route($routeName, ['task' => $task->id]), ['task_status' => $newStatus])->assertSessionDoesntHaveErrors();
        Notification::assertSentTo([$watcherUser], TaskStatusChangedNotification::class);
        Notification::assertNotSentTo([$user, $user3, $user2], TaskStatusChangedNotification::class);

        $notification = new TaskStatusChangedNotification($task, $user->id, TaskStatus::notStarted()->value, $newStatus, '');
        $notification->toMail($user);
        $data = $notification->toDatabase($user);
    }

    public function testStatusChangeCompletedNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $watcherUser = User::factory()->create();
        $watcherUser->updateSetting('email_task_status_changed_watcher', true);
        Task::withoutEvents(function () use ($task, $user, $user2) {
            $task->update(['author_id' => $user->id]);
            $task->update(['assigned_to_id' => $user2->id]);
        });
        $task->watchers()->attach($watcherUser);

        // test assignee and watchers get notified
        // in this case the default setting for email_task_status_changed_assignee is false, so the assignee should not get it
        $newStatus = TaskStatus::done()->value;
        $response = $this->put(route($routeName, ['task' => $task->id]), ['task_status' => $newStatus])->assertSessionDoesntHaveErrors();
        Notification::assertSentTo([$watcherUser], TaskCompletedNotification::class);
        Notification::assertNotSentTo([$user, $user3, $user2], TaskCompletedNotification::class);

        $notification = new TaskCompletedNotification($task, $user->id, '');
        $notification->toMail($user);
        $data = $notification->toDatabase($user);
    }

    public function testPriorityChangeNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        [$user2, $user3, $watcherUser] = $this->setupTaskAndWatchers($task, $user);

        // test assignee and watchers get notified
        $newPriority = TaskPriority::veryHigh()->value;
        $response = $this->put(route($routeName, ['task' => $task->id]), ['priority' => $newPriority])->assertSessionDoesntHaveErrors();
        Notification::assertSentTo([$user2, $watcherUser], TaskPriorityChangedNotification::class);
        Notification::assertNotSentTo([$user, $user3], TaskPriorityChangedNotification::class);

        $notification = new TaskPriorityChangedNotification($task, $user->id, null, $newPriority, '');
        $notification->toMail($user);
        $data = $notification->toDatabase($user);
    }

    public function testDueDateChangeNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        [$user2, $user3, $watcherUser] = $this->setupTaskAndWatchers($task, $user);

        // test assignee and watchers get notified
        $dueOn = now()->addDays(7);
        $response = $this->put(route($routeName, ['task' => $task->id]), ['due_on' => $dueOn->format('Y-m-d')])->assertSessionDoesntHaveErrors();
        Notification::assertSentTo([$user2, $watcherUser], TaskDueDateChangedNotification::class);
        Notification::assertNotSentTo([$user, $user3], TaskDueDateChangedNotification::class);

        $notification = new TaskDueDateChangedNotification($task, $user->id, null, (string) $dueOn, '');
        $notification->toMail($user);
        $data = $notification->toDatabase($user);
    }

    public function testAssigneeChangeNotification(): void
    {
        [$user, $task, $libryo] = $this->setupUpdateTask();
        $routeName = 'my.tasks.tasks.update';

        [$user2, $user3, $watcherUser] = $this->setupTaskAndWatchers($task, $user);

        $user4 = User::factory()->create();
        // test assignee and watchers get notified
        $response = $this->put(route($routeName, ['task' => $task->id]), ['assigned_to_id' => $user4->id])->assertSessionDoesntHaveErrors();
        Notification::assertSentTo([$watcherUser], TaskAssigneeChangedNotification::class);
        Notification::assertNotSentTo([$user, $user3, $user2], TaskAssigneeChangedNotification::class);

        $notification = new TaskAssigneeChangedNotification($task, $user->id, $user2->id, $user4->id, '');
        $notification->toMail($user);
        $data = $notification->toDatabase($user);
    }

    private function setupTaskAndWatchers($task, $user): array
    {
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $watcherUser = User::factory()->create();
        Task::withoutEvents(function () use ($task, $user, $user2) {
            $task->update(['author_id' => $user->id]);
            $task->update(['assigned_to_id' => $user2->id]);
        });
        $task->watchers()->attach($watcherUser);

        return [$user2, $user3, $watcherUser];
    }

    // ------------------------------------------------------//
    // ------------------------------------------------------//
    // ------------------------------------------------------//

    public function testTaskCreatedAssigneeNotification(): void
    {
        Notification::fake();
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $routeName = 'my.tasks.tasks.store';
        $user2 = User::factory()->create();

        $task = Task::factory()->make(['assigned_to_id' => $user2->id, 'taskable_id' => $libryo->id]);
        $count = UserActivity::count();
        // test assignee and watchers get notified
        $response = $this->post(route($routeName, ['task' => $task->id]), $task->toArray())->assertSessionDoesntHaveErrors();
        Notification::assertSentTo($user2, TaskAssignedNotification::class);
        Notification::assertNotSentTo([$user], TaskAssignedNotification::class);

        // tests wheter the CreatedTask event was dispatched and the activity count was increased
        $this->assertGreaterThan($count, UserActivity::count());

        $notification = new TaskAssignedNotification($task, $user->id, $user2->id, '');
        $notification->toMail($user);
        $data = $notification->toDatabase($user);
    }
}
