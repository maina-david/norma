<?php

namespace Tests\Unit\Mail\Tasks;

use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Mail\Tasks\TaskAssignedEmail;
use App\Mail\Tasks\TaskAssigneeChangedEmail;
use App\Mail\Tasks\TaskCompleteEmail;
use App\Mail\Tasks\TaskDueDateChangedEmail;
use App\Mail\Tasks\TaskDueEmail;
use App\Mail\Tasks\TaskPriorityChangedEmail;
use App\Mail\Tasks\TaskStatusChangedEmail;
use App\Mail\Tasks\TaskTitleChangedEmail;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Organisation;
use App\Models\Partners\WhiteLabel;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use Tests\TestCase;

class TaskNotificationEmailsTest extends TestCase
{
    // ----------------------------------------//
    // ----------------------------------------//
    // ----------------------------------------//

    private function assertSeeTaskTitle($mailable, $task): void
    {
        $mailable->assertSeeInHtml($task->title, false);
        $mailable->assertSeeInText($task->title);
    }

    private function assertSeeInHtmlAndText($mailable, $text): void
    {
        $mailable->assertSeeInHtml($text, false);
        $mailable->assertSeeInText($text);
    }

    private function setupUserTask(): array
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        return [$user, $task];
    }
    // ----------------------------------------//
    // ----------------------------------------//
    // ----------------------------------------//

    public function testTaskAssignedContents(): void
    {
        [$user, $task] = $this->setupUserTask();
        $assignee = User::factory()->create();

        $mailable = new TaskAssignedEmail($assignee, $task, $user->id);

        // see in the body
        $this->assertSeeInHtmlAndText($mailable, $user->fullName . ' assigned you a task');

        $this->assertSeeTaskTitle($mailable, $task);
    }

    public function testTaskAssignedChangedContents(): void
    {
        [$user, $task] = $this->setupUserTask();
        $changer = User::factory()->create();
        $changedTo = User::factory()->create();

        $mailable = new TaskAssigneeChangedEmail($user, $task, $user->id, $changedTo->id, $changer->id);

        // see in the body
        $this->assertSeeInHtmlAndText($mailable, 'Assignee changed for task');

        $this->assertSeeTaskTitle($mailable, $task);

        // just to check the task table is rendered
        $priorityText = TaskPriority::lang()[TaskPriority::fromValue($task->priority)->value];

        $this->assertSeeInHtmlAndText($mailable, $priorityText);
    }

    public function testTaskCompleteEmailContents(): void
    {
        [$user, $task] = $this->setupUserTask();
        $changer = User::factory()->create();

        $task->update(['author_id' => $user->id]);

        $mailable = new TaskCompleteEmail($user, $task, $changer->id);

        $this->assertSeeInHtmlAndText($mailable, 'Completed by');
        $this->assertSeeInHtmlAndText($mailable, $changer->fullName);
        $this->assertSeeInHtmlAndText($mailable, 'A task that you created has been completed');

        $this->assertSeeTaskTitle($mailable, $task);

        $task = Task::factory()->create(['assigned_to_id' => $user->id]);
        $mailable = new TaskCompleteEmail($user, $task, $changer->id);
        $this->assertSeeInHtmlAndText($mailable, 'One of your tasks has been completed');

        // assigned and author
        $task = Task::factory()->create(['assigned_to_id' => $user->id]);
        $task->update(['author_id' => $user->id]);
        $mailable = new TaskCompleteEmail($user, $task, $changer->id);
        $this->assertSeeInHtmlAndText($mailable, 'One of your tasks has been completed');

        // watcher
        $task = Task::factory()->create();
        $task->watchers()->attach($user);
        $mailable = new TaskCompleteEmail($user, $task, $changer->id);
        $this->assertSeeInHtmlAndText($mailable, 'A task you are following has been completed');
    }

    public function testTaskDueDateChangedEmailContents(): void
    {
        [$user, $task] = $this->setupUserTask();
        $changer = User::factory()->create();

        $task->update(['author_id' => $user->id]);

        $from = now()->addDays(1);
        $to = now()->addDays(3);
        $mailable = new TaskDueDateChangedEmail($user, $task, $from, $to, $changer->id);

        $this->assertSeeInHtmlAndText($mailable, 'Due date changed for task ');
        $this->assertSeeInHtmlAndText($mailable, 'Changed from ');
        $this->assertSeeInHtmlAndText($mailable, $user->formatDate($from));
        $this->assertSeeInHtmlAndText($mailable, $user->formatDate($to));

        // test link to the norma
        $this->assertSeeInHtmlAndText($mailable, $task->norma->id);
    }

    public function testTaskDueEmailContents(): void
    {
        [$user, $task] = $this->setupUserTask();

        $dueOn = now()->addMinutes(90);
        $task->update(['author_id' => $user->id, 'due_on' => $dueOn]);
        $mailable = new TaskDueEmail($user, $task);
        $mailable->build();
        $this->assertStringContainsString('A task you assigned is due today', $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'A task you assigned is due today. Its current status is ');
        $this->assertSeeInHtmlAndText($mailable, $user->formatDate($dueOn));

        $task = Task::factory()->create(['assigned_to_id' => $user->id, 'due_on' => $dueOn]);
        $mailable = new TaskDueEmail($user, $task);
        $mailable->build();
        $this->assertStringContainsString('You have a task due today', $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'One of your tasks is due today. Its current status is ');

        $task = Task::factory()->create(['due_on' => $dueOn]);
        $task->watchers()->attach($user);
        $mailable = new TaskDueEmail($user, $task);
        $mailable->build();
        $this->assertStringContainsString('A task you are watching is due today', $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'A task you are following is due today. Its current status is ');

        // Overdue tasks
        $dueOn = now()->subDays(1);
        $task = Task::factory()->create(['author_id' => $user->id, 'due_on' => $dueOn]);
        $mailable = new TaskDueEmail($user, $task);
        $mailable->build();
        $this->assertStringContainsString('A task you assigned is overdue', $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'A task you assigned is overdue. Its current status is ');
        $this->assertSeeInHtmlAndText($mailable, $user->formatDate($dueOn));

        $task = Task::factory()->create(['assigned_to_id' => $user->id, 'due_on' => $dueOn]);
        $mailable = new TaskDueEmail($user, $task);
        $mailable->build();
        $this->assertStringContainsString('You have an overdue task', $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'One of your tasks is overdue. Its current status is');

        $dueOn = now()->subDays(3);
        $task = Task::factory()->create(['assigned_to_id' => $user->id, 'due_on' => $dueOn, 'task_status' => TaskStatus::done()->value]);
        $mailable = new TaskDueEmail($user, $task);
        $mailable->build();
        $this->assertStringContainsString('Your completed task is overdue', $mailable->subject);

        $task = Task::factory()->create(['due_on' => $dueOn]);
        $task->watchers()->attach($user);
        $mailable = new TaskDueEmail($user, $task);
        $mailable->build();
        $this->assertStringContainsString('A task you are watching is overdue', $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'A task you are following is overdue. Its current status is ');
    }

    public function testTaskPriorityChangedEmailContents(): void
    {
        [$user, $task] = $this->setupUserTask();
        $changer = User::factory()->create();
        $task->update(['author_id' => $user->id]);

        $from = TaskPriority::low();
        $to = TaskPriority::high();
        $fromLang = TaskPriority::lang()[$from->value];
        $toLang = TaskPriority::lang()[$to->value];
        $mailable = new TaskPriorityChangedEmail($user, $task, $from->value, $to->value, $changer->id);
        $mailable->build();
        $this->assertStringContainsString('A task you created had it\'s priority updated to ' . $toLang, $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'Priority updated for task ');
        $mailable->assertSeeInText("Changed from {$fromLang} to {$toLang}");
    }

    public function testTaskTitleChangedEmailContents(): void
    {
        [$user, $task] = $this->setupUserTask();
        $changer = User::factory()->create();
        $task->update(['author_id' => $user->id]);

        $from = 'Something';
        $to = $task->title;
        $mailable = new TaskTitleChangedEmail($user, $task, $from, $to, $changer->id);
        $mailable->build();
        $this->assertStringContainsString('A task you created had it\'s title updated to ' . $to, $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'Title updated for task ');
        $mailable->assertSeeInText("Changed from {$from} to {$to}");
    }

    public function testTaskStatusChangedEmailContents(): void
    {
        [$user, $task] = $this->setupUserTask();
        $changer = User::factory()->create();
        $task->update(['author_id' => $user->id]);

        $from = TaskStatus::notStarted()->value;
        $to = TaskStatus::inProgress()->value;
        $mailable = new TaskStatusChangedEmail($user, $task, $from, $to, $changer->id);
        $mailable->build();
        $this->assertStringContainsString('A task you created had it\'s status updated to ' . TaskStatus::lang()[TaskStatus::inProgress()->value], $mailable->subject);
        $this->assertSeeInHtmlAndText($mailable, 'Status updated for task ');
        $mailable->assertSeeInText('Changed from ' . TaskStatus::lang()[$from] . ' to ' . TaskStatus::lang()[$to]);
    }

    public function testTaskableEmails(): void
    {
        [$user, $task] = $this->setupUserTask();
        $assignee = User::factory()->create();
        $file = File::factory()->create();
        $task->update(['taskable_id' => $file->id, 'taskable_type' => 'file']);

        $mailable = new TaskAssignedEmail($assignee, $task, $user->id);
        $mailable->assertSeeInHtml('/drives/files/');

        $reference = Reference::factory()->create();
        $task->update(['taskable_id' => $reference->id, 'taskable_type' => 'register_item']);
        $task->refresh();
        $mailable = new TaskAssignedEmail($assignee, $task, $user->id);
        $mailable->assertSeeInHtml('/requirements/citations/');

        $aiResponse = AssessmentItemResponse::factory()->create();
        $task->update(['taskable_id' => $aiResponse->id, 'taskable_type' => 'assessment_item_response']);
        $task->refresh();
        $mailable = new TaskAssignedEmail($assignee, $task, $user->id);
        $mailable->assertSeeInHtml('/assess/item/');
    }

    public function testWhitelabelsLink(): void
    {
        [$user, $task] = $this->setupUserTask();
        $assignee = User::factory()->create();
        $reference = Reference::factory()->create();
        $task->update(['taskable_id' => $reference->id, 'taskable_type' => 'register_item']);
        $whitelabel = WhiteLabel::factory()->create();
        $org = Organisation::factory()->for($whitelabel)->create(['whitelabel_id' => $whitelabel->id]);
        $assignee->organisations()->attach($org);
        $mailable = new TaskAssignedEmail($assignee, $task, $user->id);
        $mailable->assertSeeInHtml($whitelabel->shortname, false);
    }
}
