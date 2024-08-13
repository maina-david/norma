<?php

namespace Tests\Feature\Tasks;

use App\Enums\Tasks\TaskActivityType;
use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskActivity;
use Tests\Feature\My\MyTestCase;

class TaskActivityControllerTest extends MyTestCase
{
    public function testIndexForTask(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.tasks.task-activities.index';

        $task = Task::factory()->for($libryo)->create(['assigned_to_id' => $user->id]);

        $newTitle = 'New Title';
        $newStatus = TaskStatus::inProgress()->value;
        $newPriority = TaskPriority::veryHigh()->value;
        $newDue = now()->addDays(2);
        $newAssignee = User::factory()->create();
        $response = $this->put(route('my.tasks.tasks.update', ['task' => $task->id]), [
            'task_status' => $newStatus,
            'title' => $newTitle,
            'priority' => $newPriority,
            'due_on' => $newDue->format('Y-m-d'),
            'assigned_to_id' => $newAssignee->id,
        ])->assertSessionDoesntHaveErrors();

        $file = File::factory()->create();
        $this->createActivities($user, $libryo, $task, $file, $newDue);

        $response = $this->get(route($routeName, ['task' => $task]))->assertSuccessful();

        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the title from ' . $task->title . ' to ' . $newTitle . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the assignee from ' . $task->assignee->fullName . ' to ' . $newAssignee->fullName . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the task status from ' . TaskStatus::lang()[$task->task_status] . ' to ' . TaskStatus::lang()[$newStatus] . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the priority from ' . TaskPriority::lang()[$task->priority] . ' to ' . TaskPriority::lang()[$newPriority] . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the task due date from No due date to ' . $newDue->toFormattedDateString() . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' set a reminder for this task")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' posted a comment")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' uploaded ' . $file->title . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' uploaded Deleted File")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' stopped following this task")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' started following this task")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the priority from No priority set to ' . TaskPriority::lang()[TaskPriority::medium()->value] . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the priority from ' . TaskPriority::lang()[TaskPriority::medium()->value] . ' to No priority set")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the task due date from ' . $newDue->toFormattedDateString() . ' to ' . $newDue->toFormattedDateString() . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the task due date from ' . $newDue->toFormattedDateString() . ' to No due date")]]');
    }

    private function createActivities($user, $libryo, $task, $file, $newDue): void
    {
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::setReminder()->value,
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::madeComment()->value,
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::documentUpload()->value,
            'details' => ['file_id' => $file->id],
        ]);
        // unknown file
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::documentUpload()->value,
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::watchTask()->value,
            'details' => ['watching' => true],
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::watchTask()->value,
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::priorityChanged()->value,
            'details' => [
                'from_priority' => null,
                'to_priority' => TaskPriority::medium()->value,
            ],
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::priorityChanged()->value,
            'details' => [
                'from_priority' => TaskPriority::medium()->value,
                'to_priority' => null,
            ],
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::dueDateChanged()->value,
            'details' => [
                'from_due_on' => (string) $newDue,
                'to_due_on' => (string) $newDue,
            ],
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::dueDateChanged()->value,
            'details' => [
                'from_due_on' => (string) $newDue,
                'to_due_on' => null,
            ],
        ]);
        TaskActivity::factory()->for($libryo)->for($user)->for($task)->create([
            'activity_type' => TaskActivityType::assigneeChange()->value,
            'details' => [
                'from' => $user->id,
                'to' => null,
            ],
        ]);
    }
}
