<?php

namespace Tests\Unit\Notifications\Comments;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Customer\Norma;
use App\Models\Tasks\Task;
use App\Notifications\Comments\MentionedNotification;
use Tests\TestCase;

class MentionedNotificationTest extends TestCase
{
    public function testItRendersTheMail(): void
    {
        $user = User::factory()->create();
        $norma = Norma::factory()->create();
        $task = Task::factory()->create();
        $comment = Comment::withoutEvents(function () use ($norma, $task, $user) {
            return Comment::factory()->for($norma)->create(['place_id' => $norma->id, 'commentable_type' => 'task', 'commentable_id' => $task->id, 'author_id' => $user->id]);
        });
        $message = (new MentionedNotification($comment->id))->toMail($user);
        $rendered = $message->render();

        $this->assertStringContainsString('You were mentioned in a comment', $rendered);
    }
}
