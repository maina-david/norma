<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Models\Comments\Collaborate\Comment;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use Tests\TestCase;

class ReferenceCommentsControllerTest extends TestCase
{
    public function testComments(): void
    {
        $this->signIn($this->collaborateSuperUser());

        /** @var Task $task */
        $task = Task::factory()->create();
        $task->load('document.expression');

        $reference = Reference::factory()->create(['work_id' => $task->document->expression->work_id]);

        $this->postJson(route('api.collaborate.references.comments.store', ['reference' => $reference->id, 'task' => $task->id]), ['comment' => 'Testing 123'])
            ->assertSuccessful()
            ->assertJson(['data' => ['comment' => 'Testing 123']]);

        $this->getJson(route('api.collaborate.references.comments.index', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    ['comment' => 'Testing 123'],
                ],
            ]);

        $comment = Comment::where('comment', 'Testing 123')->first();

        $this->putJson(route('api.collaborate.references.comments.update', ['reference' => $reference->id, 'task' => $task->id, 'comment' => $comment->id]), ['comment' => 'Nice one'])
            ->assertSuccessful()
            ->assertJson(['data' => ['comment' => 'Nice one']]);

        $this->deleteJson(route('api.collaborate.references.comments.destroy', ['reference' => $reference->id, 'task' => $task->id, 'comment' => $comment->id]))
            ->assertSuccessful()
            ->assertJson(['data' => ['comment' => 'Nice one']]);

        $this->getJson(route('api.collaborate.references.comments.index', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertExactJson([
                'data' => [],
            ]);
    }
}
