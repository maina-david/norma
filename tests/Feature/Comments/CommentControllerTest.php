<?php

namespace Tests\Feature\Comments;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Comments\Comment;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;
use App\Notifications\Comments\MentionedNotification;
use App\Services\Customer\ActiveLibryosManager;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\My\MyTestCase;

class CommentControllerTest extends MyTestCase
{
    public function testForCommentable(): void
    {
        $user = $this->signIn($this->mySuperUser());
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);

        $this->activateAllStreams($user);
        $file = File::factory()->for($org)->create();
        $comment = Comment::factory()->create([
            'organisation_id' => $org->id,
            'commentable_type' => 'file',
            'commentable_id' => $file->id,
        ]);

        $response = $this->get(route('my.comments.for.commentable', ['type' => 'file', 'id' => $file->id]))->assertSuccessful();
        $response->assertSeeSelector('//div[text()[contains(.,"' . $comment->author->fullName . '")]]');
        $response->assertSeeSelector('//div[text()[contains(.,"' . mb_substr($comment->comment, 0, 10) . '")]]');
    }

    public function testForCommentableForLibryo(): void
    {
        $user = $this->mySuperUser();
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->create();
        $user->organisations()->attach($org);
        $user->libryos()->attach($libryo);
        $file = File::factory()->for($org)->create();
        $user2 = User::factory()->create();
        $comment = Comment::factory()->create([
            'place_id' => $libryo->id,
            'commentable_type' => 'file',
            'commentable_id' => $file->id,
            'comment' => 'Hello **user_' . $user2->id . '**, how are you',
        ]);

        $this->signIn($user);
        app(ActiveLibryosManager::class)->activate($user, $libryo);

        $response = $this->get(route('my.comments.for.commentable', ['type' => 'file', 'id' => $file->id]))->assertSuccessful();
        $response->assertSeeSelector('//div[text()[contains(.,"' . $comment->author->full_name . '")]]');
        $response->assertSeeSelector('//div[text()[contains(.,", how are you")]]');
    }

    public function testStoreForCommentable(): void
    {
        Notification::fake();
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.comments.for.commentable.store';
        $file = File::factory()->for($org)->create();
        $task = Task::factory()->create();
        $update = LegalUpdate::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->create();
        $comment = Comment::factory()->create(['commentable_type' => 'file', 'commentable_id' => $file->id]);
        $reference = Reference::factory()->create();

        $user2 = User::factory()->create();
        $commentText = 'Hello **user_' . $user2->id . '**, how are you';
        $items = [
            ['type' => 'task', 'id' => $task->id],
            ['type' => 'file', 'id' => $file->id],
            ['type' => 'update', 'id' => $update->id],
            ['type' => 'libryo', 'id' => $libryo->id],
            ['type' => 'assessment-item-response', 'id' => $aiResponse->id],
            ['type' => 'comment', 'id' => $comment->id],
            ['type' => 'reference', 'id' => $reference->id],
        ];
        foreach ($items as $item) {
            $count = Comment::count();
            $activityCount = UserActivity::count();
            $response = $this->post(route($routeName, ['type' => $item['type'], 'id' => $item['id']]), ['comment' => $commentText])
                ->assertSessionDoesntHaveErrors();
            $this->assertGreaterThan($count, Comment::count());
            $this->assertGreaterThan($activityCount, UserActivity::count(), $item['type']);
            $renderedComment = 'Hello <span class="text-primary user-mention">@' . $user2->full_name . '</span>, how are you';
            $response->assertSee('Hello');
            $response->assertSee($user2->full_name, false);
            $response->assertSee(', how are you');
            Notification::assertSentTo($user2, MentionedNotification::class);
        }

        $target = Libryo::factory()->create();
        $target->load(['organisation']);
        $question = ContextQuestion::factory()->create();

        $user->libryos()->attach($target);
        $user->organisations()->attach($target->organisation);

        $payload = [
            'save_and_back' => route('my.corpus.requirements.index'),
            'comment' => $commentText,
            'target_libryo_id' => $target->id,
        ];

        $this->post(route($routeName, ['type' => 'context-question', 'id' => $question->id]), $payload)
            ->assertRedirect(route('my.corpus.requirements.index'));

        $this->activateAllStreams($user);
        $response = $this->post(route($routeName, ['type' => 'reference', 'id' => $reference->id]), ['comment' => $commentText])
            ->assertSessionDoesntHaveErrors();
    }

    public function testDestroy(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.comments.comments.destroy';
        $file = File::factory()->for($org)->create();

        $file = File::factory()->for($org)->create();
        $comment = Comment::factory()->create([
            'place_id' => $libryo->id,
            'commentable_type' => 'file',
            'commentable_id' => $file->id,
            'author_id' => $user->id,
        ]);

        $response = $this->get(route('my.comments.for.commentable', ['type' => 'file', 'id' => $file->id]))->assertSuccessful();
        $response->assertSeeSelector('//div[text()[contains(.,"' . mb_substr($comment->comment, 0, 10) . '")]]');

        $response = $this->delete(route('my.comments.comments.destroy', ['comment' => $comment->id]))->assertSuccessful();
        $response->assertDontSeeSelector('//div[text()[contains(.,"' . mb_substr($comment->comment, 0, 10) . '")]]');

        $comment = Comment::factory()->create([
            'place_id' => $libryo->id,
            'commentable_type' => 'file',
            'commentable_id' => $file->id,
        ]);
        // non-author shouldn't be allowed to delete
        $this->withExceptionHandling()->delete(route('my.comments.comments.destroy', ['comment' => $comment->id]))
            ->assertForbidden();

        $comment->update(['author_id' => $user->id]);

        $this->withoutExceptionHandling()
            ->delete(route('my.comments.comments.destroy', ['comment' => $comment->id]), ['save_and_back' => route('my.corpus.requirements.index')])
            ->assertRedirect(route('my.corpus.requirements.index'));
    }
}
