<?php

namespace Tests\Feature\Workflows;

use App\Enums\Workflows\TaskStatus;
use App\Jobs\Workflows\NotifyTasksDueJob;
use App\Models\Collaborators\Collaborator;
use App\Models\Workflows\Task;
use App\Notifications\Workflows\TaskDueNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyTasksDueJobTest extends TestCase
{
    /**
     * @return void
     */
    public function testItNotifiesAssignee(): void
    {
        $assignee = Collaborator::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $assignee->id,
            'task_status' => TaskStatus::inReview(),
            'due_date' => now(),
        ]);

        Notification::fake();

        NotifyTasksDueJob::dispatch();

        Notification::assertNothingSent();

        $task->update(['task_status' => TaskStatus::todo()]);

        NotifyTasksDueJob::dispatch();

        Notification::assertSentTo($assignee, TaskDueNotification::class, function (TaskDueNotification $notification) use ($task, $assignee) {
            $mail = $notification->toMail($assignee);
            $mail->assertSeeInHtml('This is a reminder that you have an overdue task. Please click on the link below to attend to it as soon as possible.');

            $database = $notification->toDatabase($assignee);

            return $database['task_id'] === $task->id && $database['status'] === TaskStatus::todo()->value;
        });
    }
}
