<?php

namespace Tests\Feature\Workflows;

use App\Models\Auth\Role;
use App\Models\Collaborators\Collaborator;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use Tests\TestCase;

class TaskAssignmentControllerTest extends TestCase
{
    public function testCreatingAndRevokingApplication(): void
    {
        /** @var Role $role */
        $role = Role::factory()->collaborate()->create([
            'permissions' => ['workflows.task-application.create'],
        ]);
        /** @var Collaborator $user */
        $user = Collaborator::factory()->hasAttached($role)->create();
        $this->collaboratorSignIn($user);

        /** @var Task $task */
        $task = Task::factory()->create();

        $this->post(route('collaborate.tasks.application.create', ['task' => $task->id]))
            ->assertRedirect(route('collaborate.dashboard'));

        $this->assertDatabaseHas(TaskApplication::class, ['user_id' => $user->id, 'task_id' => $task->id]);

        $this->post(route('collaborate.tasks.application.create', ['task' => $task->id]))
            ->assertRedirect(route('collaborate.dashboard'));

        $this->assertDatabaseCount(TaskApplication::class, 1);

        $this->delete(route('collaborate.tasks.application.destroy', ['task' => $task->id]))
            ->assertRedirect(route('collaborate.dashboard'));

        $this->assertDatabaseMissing(TaskApplication::class, ['user_id' => $user->id, 'task_id' => $task->id]);
    }

    public function testAssigningAndRemovingAssignment(): void
    {
        $user = $this->collaboratorSignIn($this->collaborateSuperUser());

        /** @var Task $task */
        $task = Task::factory()->create(['user_id' => null]);

        $this->post(route('collaborate.tasks.application.assign-self', ['task' => $task->id]))
            ->assertRedirect(route('collaborate.tasks.show', ['task' => $task->id]));

        $this->assertDatabaseMissing(TaskApplication::class, ['user_id' => $user->id, 'task_id' => $task->id]);

        $this->assertSame($user->id, $task->refresh()->user_id);

        $this->delete(route('collaborate.tasks.application.remove-assignment-from-self', ['task' => $task->id]))
            ->assertRedirect(route('collaborate.tasks.show', ['task' => $task->id]));

        $this->assertDatabaseMissing(TaskApplication::class, ['user_id' => $user->id, 'task_id' => $task->id]);

        $this->assertNull($task->refresh()->user_id);
    }
}
