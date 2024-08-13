<?php

namespace Tests\Feature\Comments\Collaborate;

use App\Models\Auth\Role;
use App\Models\Collaborators\Collaborator;
use App\Models\Comments\Collaborate\Comment;
use App\Models\Corpus\Reference;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskType;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testProvidesComments(): void
    {
        $this->signIn($this->collaborateSuperUser());
        $task = Task::factory()->create();
        $reference = Reference::factory()->create();
        $comments = Comment::factory()->count(3)->create(['task_id' => $task->id]);
        $referenceComments = Comment::factory()->count(3)->create(['task_id' => $task->id, 'reference_id' => $reference->id]);

        $this->get(route('collaborate.comments.index', ['task' => $task]))
            ->assertSuccessful()
            ->assertTurboStream(function (AssertableTurboStream $streams) use ($referenceComments, $comments, $task) {
                return $streams->has(1)
                    ->hasTurboStream(function ($stream) use ($referenceComments, $comments, $task) {
                        $stream->where('target', "comments_workflows_task_{$task->id}")
                            ->where('action', 'update');

                        $comments->each(fn ($comment) => $stream->see($comment->comment));
                        $referenceComments->each(fn ($comment) => $stream->see($comment->comment));

                        return $stream;
                    });
            });

        $response = $this->get(route('collaborate.comments.index', ['task' => $task, 'reference' => $reference]))
            ->assertSuccessful();

        $comments->each(fn ($comment) => $response->assertDontSee($comment->comment));

        $response->assertTurboStream(function (AssertableTurboStream $streams) use ($reference, $referenceComments) {
            return $streams->has(1)
                ->hasTurboStream(function ($stream) use ($reference, $referenceComments) {
                    $stream->where('target', "comments_corpus_reference_{$reference->id}")
                        ->where('action', 'update');

                    $referenceComments->each(fn ($comment) => $stream->see($comment->comment));

                    return $stream;
                });
        });
    }

    /**
     * @return void
     */
    public function testAllowsDeletionOfCommentsForTask(): void
    {
        $type = TaskType::factory()->create();
        $role = Role::factory()->collaborate()->create(['permissions' => [$type->permission(false)]]);
        $user = Collaborator::factory()->hasAttached($role)->create();
        $this->signIn($user);

        $task = Task::factory()->create(['task_type_id' => $type->id, 'user_id' => $user->id]);
        $comments = Comment::factory()->count(3)->create(['task_id' => $task->id]);

        $this->applyTaskTypesPermissions();

        $this->get(route('collaborate.comments.index', ['task' => $task]))
            ->assertSuccessful()
            ->assertDontSee('Edit')
            ->assertDontSee('Delete')
            ->assertTurboStream(function (AssertableTurboStream $streams) use ($comments, $task) {
                return $streams->has(1)
                    ->hasTurboStream(function ($stream) use ($comments, $task) {
                        $stream->where('target', "comments_workflows_task_{$task->id}")
                            ->where('action', 'update');

                        $comments->each(fn ($comment) => $stream->see($comment->comment));

                        return $stream;
                    });
            });

        $comments->each->update(['author_id' => $user->id]);

        $this->get(route('collaborate.comments.index', ['task' => $task]))
            ->assertSuccessful()
            ->assertSee('Edit')
            ->assertSee('Delete');

        $delete = $comments->first();

        $this->assertDatabaseHas(Comment::class, ['id' => $delete->id]);

        $this->delete(route('collaborate.comments.destroy', $delete->id))
            ->assertRedirect(route('collaborate.comments.index', ['task' => $task]));

        $this->assertDatabaseMissing(Comment::class, ['id' => $delete->id]);
    }

    /**
     * @return void
     */
    public function testAllowsPostingComment(): void
    {
        $type = TaskType::factory()->create();
        $role = Role::factory()->collaborate()->create(['permissions' => [$type->permission(false)]]);
        $user = Collaborator::factory()->hasAttached($role)->create();
        $this->signIn($user);

        $task = Task::factory()->create(['task_type_id' => $type->id, 'user_id' => $user->id]);

        $this->applyTaskTypesPermissions();

        $this->assertDatabaseMissing(Comment::class, ['comment' => 'Awesome Libryo!']);

        $this->post(route('collaborate.comments.store', ['task' => $task]), ['comment' => 'Awesome Libryo!'])
            ->assertRedirect(route('collaborate.comments.index', ['task' => $task]));

        $this->assertDatabaseHas(Comment::class, ['comment' => 'Awesome Libryo!']);

        $this->get(route('collaborate.comments.index', ['task' => $task]))
            ->assertSuccessful()
            ->assertSee('Edit')
            ->assertSee('Delete')
            ->assertTurboStream(function (AssertableTurboStream $streams) use ($task) {
                return $streams->has(1)
                    ->hasTurboStream(function ($stream) use ($task) {
                        return $stream->where('target', "comments_workflows_task_{$task->id}")
                            ->where('action', 'update')
                            ->see('Awesome Libryo!');
                    });
            });

        /** @var Comment $comment */
        $comment = Comment::where('author_id', $user->id)->where('comment', 'Awesome Libryo!')->firstOrFail();

        $this->get(route('collaborate.comments.edit', ['comment' => $comment->id]), ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertTurboStream(function (AssertableTurboStream $streams) {
                return $streams->has(1)
                    ->hasTurboStream(function ($stream) {
                        return $stream->where('action', 'update')->see('Awesome Libryo!');
                    });
            });

        $this->put(route('collaborate.comments.update', ['comment' => $comment->id]), ['comment' => 'Awesome Zebra Corn'])
            ->assertRedirect(route('collaborate.comments.index', ['task' => $task]));

        $this->assertDatabaseMissing(Comment::class, ['comment' => 'Awesome Libryo!']);

        $this->assertDatabaseHas(Comment::class, ['comment' => 'Awesome Zebra Corn']);
    }
}
