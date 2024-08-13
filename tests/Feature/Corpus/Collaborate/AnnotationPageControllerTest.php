<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Enums\Workflows\CollaborateSessionKey;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use Tests\TestCase;

class AnnotationPageControllerTest extends TestCase
{
    protected mixed $type;

    /**
     * @return void
     */
    public function testItGuardsCorrectly(): void
    {
        $role = Role::factory()->collaborate()->create();
        $user = User::factory()->hasAttached($role)->create();
        $this->signIn($user);

        $expression = WorkExpression::factory()->create();
        $document = Document::create(['work_id' => $expression->work_id, 'work_expression_id' => $expression->id]);
        $parent = Task::factory()->create(['document_id' => $document->id]);
        $task = Task::factory()->create(['document_id' => $document->id, 'parent_task_id' => $parent->id]);
        $item = AssessmentItem::factory()->create();

        $this->applyTaskTypesPermissions();

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.preview', ['expression' => $expression->id]))
            ->assertSuccessful();

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.index', ['expression' => $expression->id]))
            ->assertForbidden();

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.index', ['expression' => $expression->id, 'task' => $task->id]))
            ->assertForbidden();

        $task->update(['user_id' => $user->id]);

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.index', ['expression' => $expression->id]))
            ->assertForbidden();

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.index', ['expression' => $expression->id, 'task' => $task->id]))
            ->assertForbidden();

        $role->update(['permissions' => [...$role->refresh()->permissions, $task->taskType->permission(false), 'corpus.work-expression.annotate']]);
        $user->flushComputedPermissions();

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.index', ['expression' => $expression->id]))
            ->assertForbidden();

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.index', ['expression' => $expression->id, 'task' => $task->id]))
            ->assertSessionHas(CollaborateSessionKey::CURRENT_TASK->value);

        $role->update(['permissions' => [...$role->refresh()->permissions, 'corpus.work-expression.annotate-without-task']]);
        $user->flushComputedPermissions();

        $this->withExceptionHandling()
            ->get(route('collaborate.annotations.index', ['expression' => $expression->id]))
            ->assertSessionMissing(CollaborateSessionKey::CURRENT_TASK->value);

        $this->get(route('collaborate.annotations.index', ['expression' => $expression->id, 'assessmentItems' => [$item->id]]))
            ->assertSuccessful();
    }

    /**
     * @return void
     */
    public function testCheckRedirection(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();

        $this->assertNull($expression->work->active_work_expression_id);

        $this->get(route('collaborate.annotations.work.index', $expression->work_id))
            ->assertRedirect(route('collaborate.annotations.index', $expression->id));

        $newExp = WorkExpression::factory()->create(['work_id' => $expression->work_id]);

        $expression->work->update(['active_work_expression_id' => $newExp->id]);

        $this->get(route('collaborate.annotations.work.index', $newExp->work_id))
            ->assertRedirect(route('collaborate.annotations.index', $newExp->id));

        $this->get(route('collaborate.annotations.register.index', $newExp->work_id))
            ->assertRedirect(route('collaborate.annotations.index', $newExp->id));
    }
}
