<?php

namespace Tests\Feature\Api\Internals\My\Comments;

use App\Models\Comments\Comment;
use App\Models\Tasks\Task;
use Tests\Feature\My\MyTestCase;

class CommentControllerTest extends MyTestCase
{
    public function testCreationAndListing(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $task = Task::factory()->create(['place_id' => $libryo->id]);

        $this->assertDatabaseMissing(Comment::class, ['commentable_type' => 'task', 'commentable_id' => $task->id, 'comment' => 'test comment']);

        $this->postJson(route('api.my.comments.related.store', ['relation' => 'task', 'related' => $task->id]), ['comment' => 'test comment'])
            ->assertSuccessful();

        $this->assertDatabaseHas(Comment::class, ['commentable_type' => 'task', 'commentable_id' => $task->id, 'comment' => 'test comment']);

        $this->getJson(route('api.my.comments.related.index', ['relation' => 'task', 'related' => $task->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    ['comment' => 'test comment'],
                ],
            ]);

        $comment = Comment::where('commentable_type', 'task')->where('commentable_id', $task->id)->first();

        $this->deleteJson(route('api.my.comments.destroy', ['comment' => $comment->id]))
            ->assertSuccessful();

        $this->assertDatabaseMissing(Comment::class, ['commentable_type' => 'task', 'commentable_id' => $task->id, 'comment' => 'test comment']);
    }
}
