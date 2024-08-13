<?php

namespace Tests\Unit\Notifications\Comments;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Customer\Libryo;
use App\Models\Tasks\Task;
use App\Notifications\Comments\CommentReplyNotification;
use Tests\TestCase;

class CommentReplyNotificationTest extends TestCase
{
    public function testItRendersTheMail(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $libryo = Libryo::factory()->create();
        $task = Task::factory()->create();
        $comment = Comment::withoutEvents(function () use ($libryo, $task, $user, $user2) {
            $commentOn = Comment::factory()->for($libryo)->create(['place_id' => $libryo->id, 'commentable_type' => 'task', 'commentable_id' => $task->id, 'author_id' => $user->id]);

            return Comment::factory()->for($libryo)->create(['place_id' => $libryo->id, 'commentable_type' => 'comment', 'commentable_id' => $commentOn->id, 'author_id' => $user2->id]);
        });
        $message = (new CommentReplyNotification($comment->id))->toMail($user2);
        $rendered = $message->render();

        $this->assertStringContainsString('Someone replied to one of your comments', $rendered);
    }
}
