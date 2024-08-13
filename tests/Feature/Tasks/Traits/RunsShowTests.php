<?php

namespace Tests\Feature\Tasks\Traits;

use App\Models\Auth\User;
use App\Models\Tasks\Task;

trait RunsShowTests
{
    private function runShowTest(string $routeName): array
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->for($libryo)->create(['author_id' => $user->id]);

        $this->signIn($user);
        $response = $this->get(route($routeName, ['task' => $task]))->assertSuccessful();
        $response->assertSeeSelector('//h3[text()[contains(.,"' . $task->title . '")]]');
        //        $response->assertSeeSelector('//div[text()[contains(.,"' . $task->author->fullName . '")]]');
        $response->assertSeeSelector('//button[text()[contains(.,"Edit")]]');

        $user2 = User::factory()->create();
        $task = Task::factory()->for($libryo)->create();
        $task->update(['author_id' => $user2->id]);

        $response = $this->get(route($routeName, ['task' => $task]))->assertSuccessful();
        $response->assertDontSeeSelector('//a[text()[contains(.,"Edit")]]');

        $user2 = User::factory()->create();
        $task = Task::factory()->create(['author_id' => $user2->id]);
        $response = $this->withExceptionHandling()
            ->get(route($routeName, ['task' => $task]))
            ->assertForbidden();

        return [$user, $libryo, $org];
    }
}
