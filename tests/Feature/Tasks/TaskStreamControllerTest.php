<?php

namespace Tests\Feature\Tasks;

use App\Enums\Tasks\TaskStatus;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Models\Notify\LegalUpdate;
use App\Models\Tasks\Task;
use App\Services\Customer\ActiveLibryosManager;
use Tests\Feature\My\MyTestCase;
use Tests\Feature\Tasks\Traits\RunsShowTests;
use Tests\Feature\Traits\TestsVisibleFormLabels;
use Tests\Traits\UsesLibryoAI;

class TaskStreamControllerTest extends MyTestCase
{
    use RunsShowTests;
    use TestsVisibleFormLabels;
    use UsesLibryoAI;

    public function testStreamShow(): void
    {
        $routeName = 'my.tasks.tasks.stream.show';
        $this->runShowTest($routeName);
    }

    public function testIndexForRelatedAssessmentItemResponse(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $work = Work::factory()->hasReferences(2)->create();
        $reference = $work->references->first();

        $reference->libryos()->attach($libryo);
        $assessmentItem = AssessmentItem::factory()->create();
        $assessmentItemResponse = AssessmentItemResponse::factory()->for($libryo)->for($assessmentItem)->create();
        $reference->assessmentItems()->attach($assessmentItem);
        $task = Task::factory()->for($libryo)->create([
            'author_id' => $user->id,
            'taskable_type' => 'assessment_item_response',
            'taskable_id' => $assessmentItemResponse->id,
        ]);

        $routeName = 'my.tasks.tasks.for.related.index';
        $response = $this->get(route($routeName, ['relation' => 'assessment-item-response', 'id' => $assessmentItemResponse->id]))->assertSuccessful();
        $response->assertSee($task->title);

        app(ActiveLibryosManager::class)->activateAll($user);

        $response = $this->get(route($routeName, ['relation' => 'assessment-item-response', 'id' => $assessmentItemResponse->id]))->assertSuccessful();
        $response->assertSee($task->title);
    }

    public function testIndexForRelatedReference(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $work = Work::factory()->hasReferences(2)->create();
        $reference = $work->references->first();
        $reference->libryos()->attach($libryo);
        $task = Task::factory()->for($libryo)->create([
            'author_id' => $user,
            'taskable_type' => 'register_item',
            'taskable_id' => $reference->id,
        ]);

        $routeName = 'my.tasks.tasks.for.related.index';
        $response = $this->get(route($routeName, ['relation' => 'reference', 'id' => $reference->id]))->assertSuccessful();
        $response->assertSee($task->title);

        app(ActiveLibryosManager::class)->activateAll($user);

        $response = $this->get(route($routeName, ['relation' => 'reference', 'id' => $reference->id]))->assertSuccessful();
        $response->assertSee($task->title);
    }

    public function testIndexForRelatedUpdate(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $update = LegalUpdate::factory()->create();
        $task = Task::factory()->for($libryo)->create([
            'author_id' => $user,
            'taskable_type' => 'register_notification',
            'taskable_id' => $update->id,
        ]);

        $routeName = 'my.tasks.tasks.for.related.index';
        $response = $this->get(route($routeName, ['relation' => 'update', 'id' => $update->id]))->assertSuccessful();
        $response->assertSee($task->title);

        app(ActiveLibryosManager::class)->activateAll($user);

        $response = $this->get(route($routeName, ['relation' => 'update', 'id' => $update->id]))->assertSuccessful();
        $response->assertSee($task->title);
    }

    public function testCreateForRelated(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $work = Work::factory()->hasReferences(2)->create();
        $reference = $work->references->first();

        $reference->libryos()->attach($libryo);
        $assessmentItem = AssessmentItem::factory()->create();
        $assessmentItemResponse = AssessmentItemResponse::factory()->for($libryo)->for($assessmentItem)->create();
        $reference->assessmentItems()->attach($assessmentItem);

        $routeName = 'my.tasks.tasks.for.related.create';

        $this->assertSeeVisibleFormLabels(
            [],
            route($routeName, ['relation' => 'assessment-item-response', 'id' => $assessmentItemResponse->id, 'libryo' => $libryo->id]),
            ['Title', 'Description', 'Status', 'Due On', 'Priority', 'Followers']
        );
    }

    public function testFollowAndUnfollow(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $update = LegalUpdate::factory()->create();
        $task = Task::factory()->for($libryo)->create([
            'author_id' => $user,
            'taskable_type' => 'register_notification',
            'taskable_id' => $update->id,
        ]);

        $this->put(route('my.tasks.tasks.update', ['task' => $task]), ['followers' => [$user->id]])
            ->assertSessionHasNoErrors();

        $this->put(route('my.tasks.tasks.update', ['task' => $task]), ['followers' => []])
            ->assertSessionHasNoErrors();

        $routeName = 'my.tasks.tasks.follow';
        $response = $this->get(route($routeName, ['task' => $task]))->assertSuccessful();

        // see the eye-slash icon for unfollowing a task
        //        $response->assertSeeSelector('//a//i[contains(@class,"fa-eye-slash")]');

        $routeName = 'my.tasks.tasks.unfollow';
        $response = $this->get(route($routeName, ['task' => $task]))->assertSuccessful();

        // see the eye icon for unfollowing a task
        //        $response->assertSeeSelector('//a//i[contains(@class,"fa-eye")]');
        //        $response->assertDontSeeSelector('//a//i[contains(@class,"fa-eye-slash")]');
    }

    public function testUpdateStatus(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $update = LegalUpdate::factory()->create();
        $task = Task::factory()->for($libryo)->create([
            'author_id' => $user,
            'taskable_type' => 'register_notification',
            'taskable_id' => $update->id,
        ]);

        $routeName = 'my.tasks.tasks.task-status.update';
        $response = $this->patch(route($routeName, ['task' => $task]), ['task_status' => TaskStatus::inProgress()->value])->assertSuccessful();

        $response->assertSee('In Progress');
        $this->assertTrue($task->refresh()->task_status === TaskStatus::inProgress()->value);
    }

    public function testSuggestForRelated(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $work = Work::factory()->hasReferences(2)->create();
        $reference = $work->references->first();

        $reference->libryos()->attach($libryo);
        $assessmentItem = AssessmentItem::factory()->create();
        $assessmentItemResponse = AssessmentItemResponse::factory()->for($libryo)->for($assessmentItem)->create();
        $reference->assessmentItems()->attach($assessmentItem);

        ReferenceContent::factory()->create(['reference_id' => $reference->id]);

        $response = $this->get(route('my.tasks.tasks.for.related.suggest',
            ['relation' => 'assessment-item-response', 'id' => $assessmentItemResponse->id, 'libryo' => $libryo->id])
        )->assertSuccessful();
    }
}
