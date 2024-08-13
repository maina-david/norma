<?php

namespace Tests\Feature\Collaborators;

use App\Models\Collaborators\Collaborator;
use App\Models\Workflows\Task;
use App\Notifications\Workflows\TaskAssignedNotification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $user = $this->signIn($this->collaborateSuperUser());
        $collaborator = Collaborator::find($user->id);
        $task = Task::factory()->create(['user_id' => $collaborator->id]);
        $collaborator->notify(new TaskAssignedNotification($task));

        $routeName = 'collaborate.collaborators.notifications.index';
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee($task->id);
        $response->assertSee($task->title);
    }
}
