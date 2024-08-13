<?php

namespace Tests\Feature\Workflows;

use App\Enums\Corpus\WorkStatus;
use App\Enums\Workflows\TaskStatus;
use App\Events\Workflows\TaskAssigneeChanged;
use App\Events\Workflows\TaskStatusChanged;
use App\Jobs\Corpus\CacheWorkContent;
use App\Jobs\Corpus\GenerateReferenceContentExtracts;
use App\Mail\Workflows\TaskFeedbackEmail;
use App\Models\Collaborators\Collaborator;
use App\Models\Comments\Collaborate\Comment;
use App\Models\Corpus\Work;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use App\Models\Workflows\TaskTransition;
use App\Models\Workflows\TaskType;
use App\Notifications\Workflows\TaskAssignedNotification;
use App\Notifications\Workflows\TaskStatusChangedNotification;
use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskObserverTest extends TestCase
{
    /**
     * @return void
     */
    public function testFiringOfSavedEvents(): void
    {
        Event::fake([
            TaskAssigneeChanged::class,
            TaskStatusChanged::class,
        ]);

        Event::assertNothingDispatched();

        $this->assertDatabaseCount(TaskTransition::class, 0);

        $this->signIn($this->collaborateSuperUser());

        $task = Task::factory()->create();

        $this->assertDatabaseHas(TaskTransition::class, ['task_id' => $task->id, 'transitioned_field' => 'task_id']);

        Event::assertDispatched(TaskAssigneeChanged::class, fn ($e) => $e->task->id === $task->id);
        Event::assertNotDispatched(TaskStatusChanged::class);
    }

    /**
     * @return void
     */
    public function testFiringOfUpdatedEvents(): void
    {
        Event::fake([
            TaskAssigneeChanged::class,
            TaskStatusChanged::class,
        ]);

        Event::assertNothingDispatched();

        $task = Task::factory()->create();

        Event::assertDispatched(TaskAssigneeChanged::class, fn ($e) => $e->task->id === $task->id);
        Event::assertNotDispatched(TaskStatusChanged::class);

        Event::fake([
            TaskAssigneeChanged::class,
            TaskStatusChanged::class,
        ]);

        $task->update(['task_status' => TaskStatus::inProgress()]);

        Event::assertNotDispatched(TaskAssigneeChanged::class);
        Event::assertDispatched(TaskStatusChanged::class, fn ($e) => $e->task->id === $task->id);
    }

    /**
     * @return void
     */
    public function testDeletingParent(): void
    {
        $parent = Task::factory()->create(['parent_task_id' => null]);
        $tasks = Task::factory()->count(3)->create(['parent_task_id' => $parent->id]);

        $parent->delete();

        $tasks->each(fn ($task) => $this->assertNotNull($task->refresh()->deleted_at));
    }

    /**
     * @return void
     */
    public function testChangingAssignee(): void
    {
        Notification::fake();

        $this->signIn($this->collaborateSuperUser());

        $assignee = Collaborator::factory()->create();

        $task = Task::factory()->create(['user_id' => null]);

        TaskApplication::factory()->count(5)->create(['task_id' => $task->id]);

        Notification::assertNothingSent();

        $this->assertDatabaseCount(TaskApplication::class, 5);

        $this->assertDatabaseMissing(TaskTransition::class, ['user_id' => $assignee->id]);

        $task->update(['user_id' => $assignee->id]);

        $this->assertDatabaseHas(TaskTransition::class, ['user_id' => $assignee->id]);

        $this->assertDatabaseCount(TaskApplication::class, 0);

        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    /**
     * @return void
     */
    public function testChangingComplexity(): void
    {
        $task = Task::factory()->create(['complexity' => null]);

        $this->assertDatabaseMissing(TaskTransition::class, ['task_id' => $task->id, 'transitioned_field' => 'complexity']);

        $task->update(['complexity' => 2]);

        $this->assertDatabaseHas(TaskTransition::class, ['task_id' => $task->id, 'transitioned_field' => 'complexity']);
    }

    /**
     * @return void
     */
    public function testChangingDocument(): void
    {
        $parent = Task::factory()->create(['parent_task_id' => null, 'document_id' => null]);
        $tasks = Task::factory()->count(3)->create(['parent_task_id' => $parent->id, 'document_id' => null]);

        $tasks->each(fn ($task) => $this->assertNull($task->document_id));

        $document = Document::create([]);

        $parent->update(['document_id' => $document->id]);

        $this->assertSame($document->id, $parent->refresh()->document_id);

        $tasks->each(fn ($task) => $this->assertSame($document->id, $task->refresh()->document_id));

        $newDoc = Document::create([]);

        $tasks->first()->refresh()->update(['document_id' => $newDoc->id]);

        $this->assertSame($newDoc->id, $parent->refresh()->document_id);

        $tasks->each(fn ($task) => $this->assertSame($newDoc->id, $task->refresh()->document_id));
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function testChangingStatus(): void
    {
        $work = Work::factory()->create(['status' => WorkStatus::pending()]);
        $document = Document::create(['work_id' => $work->id]);
        $taskType = TaskType::factory()->create();
        $board = Board::factory()->create([
            'task_type_defaults' => [
                $taskType->id => [
                    'cache_related_work' => true,
                    'cache_related_work_trigger' => TaskStatus::done()->value,
                    'publish_related_work' => true,
                    'publish_related_work_trigger' => TaskStatus::done()->value,
                    'generate_reference_extracts' => true,
                    'generate_reference_extracts_trigger' => TaskStatus::done()->value,
                ],
            ],
        ]);

        $this->signIn($this->collaborateSuperUser());

        $assignee = Collaborator::factory()->create();

        $task = Task::factory()->create([
            'document_id' => $document->id,
            'board_id' => $board->id,
            'task_type_id' => $taskType->id,
            'user_id' => $assignee->id,
            'task_status' => TaskStatus::pending()->value,
        ]);

        $children = Task::factory()->count(3)->create([
            'parent_task_id' => $task->id,
            'task_status' => TaskStatus::todo()->value,
        ]);

        $mail = new TaskFeedbackEmail($task);

        $mail->assertSeeInHtml('Thanks for submitting your task for review.');

        Notification::fake();
        Mail::fake();

        Notification::assertNothingSent();

        $this->assertDatabaseMissing(TaskTransition::class, ['task_id' => $task->id, 'transitioned_field' => 'task_status']);

        $task->refresh()->update(['task_status' => TaskStatus::todo()->value]);

        $this->assertDatabaseHas(TaskTransition::class, ['task_id' => $task->id, 'transitioned_field' => 'task_status']);

        Notification::assertSentTo($assignee, TaskStatusChangedNotification::class);

        Bus::fake([CacheWorkContent::class, GenerateReferenceContentExtracts::class]);

        Bus::assertNotDispatched(CacheWorkContent::class);
        Bus::assertNotDispatched(GenerateReferenceContentExtracts::class);

        $task->refresh()->update(['task_status' => TaskStatus::done()->value]);

        $this->assertSame(WorkStatus::active()->value, $work->refresh()->status);

        Mail::assertNotOutgoing(TaskFeedbackEmail::class);

        $task->refresh()->update(['task_status' => TaskStatus::inReview()->value]);
        $task->refresh()->update(['task_status' => TaskStatus::done()->value]);

        Bus::assertDispatched(CacheWorkContent::class);
        Bus::assertDispatched(GenerateReferenceContentExtracts::class);

        Mail::assertQueued(TaskFeedbackEmail::class);

        $children->each(fn ($child) => $child->update(['task_status' => TaskStatus::inReview()->value]));
        $task->refresh()->update(['task_status' => TaskStatus::archive()->value]);

        $children->each(fn ($child) => $this->assertSame(TaskStatus::inReview()->value, $child->refresh()->task_status));

        $task->refresh()->update(['task_status' => TaskStatus::done()->value]);

        $children->each(fn ($child) => $child->update(['task_status' => TaskStatus::pending()->value]));
        $task->refresh()->update(['task_status' => TaskStatus::archive()->value]);

        $children->each(fn ($child) => $this->assertSame(TaskStatus::archive()->value, $child->refresh()->task_status));
    }

    /**
     * @return void
     */
    public function testChangingTaskType(): void
    {
        $taskType = TaskType::factory()->create();
        $newTaskType = TaskType::factory()->create();
        $task = Task::factory()->create([
            'task_type_id' => $taskType->id,
        ]);

        $comments = Comment::factory()->create(['task_type_id' => $task->task_type_id, 'task_id' => $task->id]);

        $task->update(['task_type_id' => $newTaskType->id]);

        $comments->each(fn ($comm) => $this->assertSame($newTaskType->id, $comm->refresh()->task_type_id));
    }
}
