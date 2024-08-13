<?php

namespace Tests\Unit\Actions\Comments\Comment;

use App\Actions\Comments\Comment\ExtractMentions;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Tasks\Task;
use Tests\TestCase;

class ExtractMentionsTest extends TestCase
{
    public function testHandle(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $task = Task::factory()->create();
        $comment = Comment::factory()->create([
            'comment' => 'hello **user_' . $user->id . '**, how are you, and how are you **user_' . $user2->id . '** and then this is not a correct mention *user_' . $user3->id . '* ',
            'commentable_type' => 'task',
            'commentable_id' => $task->id,
        ]);
        $users = app(ExtractMentions::class)->handle($comment);
        $this->assertTrue($users->contains($user));
        $this->assertTrue($users->contains($user2));
        $this->assertFalse($users->contains($user3));
    }
}
