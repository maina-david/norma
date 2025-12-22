<?php

namespace Tests\Feature\Tasks;

use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Tasks\Task;
use App\Notifications\Tasks\TaskDueNotification;
use Artisan;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyDueTasksTest extends TestCase
{
    /**
     * @return void
     */
    public function testItGeneratesTheCorrectTranslationTitle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Task $task */
        $task = Task::factory()->create([
            'due_on' => now()->addHour(),
            'task_status' => TaskStatus::notStarted()->value,
        ]);

        $this->assertSame('notifications.task_due_subject', $task->getNotificationSubjectTranslationKey($user));

        $task->watchers()->attach($user->id);

        unset($task->watchers);

        $this->assertSame(
            'notifications.task_due_watching_subject',
            $task->getNotificationSubjectTranslationKey($user)
        );

        $task->author_id = $user->id;

        unset($task->author);

        $this->assertSame(
            'notifications.task_due_assigned_subject',
            $task->getNotificationSubjectTranslationKey($user)
        );

        $task->assigned_to_id = $user->id;

        unset($task->assignee);

        $this->assertSame(
            'notifications.task_due_incomplete_assignee_subject',
            $task->getNotificationSubjectTranslationKey($user)
        );
    }

    /**
     * @return void
     */
    public function testCorrectUsersAreNotified(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['timezone' => 'UTC']);
        /** @var User $watcher */
        $watcher = User::factory()->create(['timezone' => 'UTC']);
        /** @var User $author */
        $author = User::factory()->create(['timezone' => 'UTC']);

        /** @var Task $task */
        $task = Task::factory()->create([
            'assigned_to_id' => $user,
            'author_id' => $author,
            'due_on' => now()->addHour(),
            'task_status' => TaskStatus::notStarted()->value,
        ]);
        $task->watchers()->attach($watcher->id);

        $notifiables = $task->getNotifiables();
        $this->assertCount(3, $notifiables);

        $this->assertContains($user->id, $notifiables->pluck('id'));
        $this->assertContains($watcher->id, $notifiables->pluck('id'));
        $this->assertContains($author->id, $notifiables->pluck('id'));

        Notification::fake();

        Notification::assertNothingSent();

        $this->travelTo(now()->setTime(8, 0));

        Artisan::call('norma:notify-due-tasks');

        Notification::assertSentTo([$user], TaskDueNotification::class);
        Notification::assertSentTo([$watcher], TaskDueNotification::class);
        Notification::assertSentTo([$author], TaskDueNotification::class);
    }

    public function testMail(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();
        $key = $task->getNotificationSubjectTranslationKey($user);
        $notification = new TaskDueNotification($task, TaskStatus::notStarted()->value, now()->toDateString(), $user->id, $key, []);
        $arr = $notification->databaseFormat($user);
        $this->assertSame($task->id, $arr['task_id']);
        $this->assertSame(TaskStatus::notStarted()->value, $arr['status']);

        $this->assertInstanceOf(Mailable::class, $notification->toMail($user));
    }
}
