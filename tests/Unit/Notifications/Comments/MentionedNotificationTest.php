<?php

namespace Tests\Unit\Notifications\Comments;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Customer\Libryo;
use App\Models\Tasks\Task;
use App\Notifications\Comments\MentionedNotification;
use Tests\TestCase;

class MentionedNotificationTest extends TestCase
{
    public function testItRendersTheMail(): void
    {
        $user = User::factory()->create();
        $libryo = Libryo::factory()->create();
        $task = Task::factory()->create();
        $comment = Comment::withoutEvents(function () use ($libryo, $task, $user) {
            return Comment::factory()->for($libryo)->create(['place_id' => $libryo->id, 'commentable_type' => 'task', 'commentable_id' => $task->id, 'author_id' => $user->id]);
        });
        $message = (new MentionedNotification($comment->id))->toMail($user);
        $rendered = $message->render();

        $this->assertStringContainsString('You were mentioned in a comment', $rendered);
    }
}
