<?php

namespace Tests\Unit\Services\Comments;

use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Corpus\Reference;
use App\Services\Comments\CommentReplacements;
use Tests\TestCase;

class CommentReplacementsTest extends TestCase
{
    public function testReplaceMentions(): void
    {
        $user = User::factory()->create();
        $ref = Reference::factory()->create();
        $comments = Comment::factory(3)->create(['commentable_type' => 'register_item', 'commentable_id' => $ref->id]);
        $comments[0]->update(['comment' => 'Test comment **user_' . $user->id . '**']);
        $comments = app(CommentReplacements::class)->replaceContent($comments);
        $this->assertStringContainsString($user->fname, $comments[0]->comment);
        $this->assertStringContainsString($user->sname, $comments[0]->comment);
    }

    public function testReplaceEmojis(): void
    {
        $user = User::factory()->create();
        $ref = Reference::factory()->create();
        $comments = Comment::factory(3)->create(['commentable_type' => 'register_item', 'commentable_id' => $ref->id]);
        $comments[0]->update(['comment' => 'Test comment ~|😀|~']);
        $comments = app(CommentReplacements::class)->replaceContent($comments);
        $this->assertStringContainsString('😀', $comments[0]->comment);
        $this->assertStringNotContainsString('~|', $comments[0]->comment);
        $this->assertStringNotContainsString('|~', $comments[0]->comment);
    }
}
