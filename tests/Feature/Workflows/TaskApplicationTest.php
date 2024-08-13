<?php

namespace Tests\Feature\Workflows;

use App\Mail\Workflows\TaskApplicationCreatedEmail;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskApplication;
use App\Models\Workflows\TaskType;
use Tests\Feature\Abstracts\CrudTestCase;

class TaskApplicationTest extends CrudTestCase
{
    protected bool $searchable = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return TaskApplication::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.task-applications';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['task.title', 'applicant.fname'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Applicant', 'Task'];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array<int, string>
     */
    protected static function excludeActions(): array
    {
        return ['create', 'store', 'edit', 'update', 'destroy', 'show'];
    }

    /**
     * @return void
     */
    public function testCanApproveApplications(): void
    {
        $task = Task::factory()->create(['user_id' => null]);
        $application = TaskApplication::factory()->create(['task_id' => $task->id]);

        $this->assertNull($task->user_id);

        $route = route('collaborate.task-applications.update', ['task_application' => $application->id]);

        $this->validateAuthGuard($route, 'put');

        $this->assertNull($task->refresh()->user_id);

        $this->signIn();

        $this->validateCollaborateRole($route, 'put');

        $this->assertNotNull($task->refresh()->user_id);

        $this->assertEquals($application->user_id, $task->user_id);
    }

    /**
     * @return void
     */
    public function testCanRejectApplications(): void
    {
        $task = Task::factory()->create(['user_id' => null]);
        $application = TaskApplication::factory()->create(['task_id' => $task->id]);

        $route = route('collaborate.task-applications.destroy', ['task_application' => $application->id]);

        $this->validateAuthGuard($route, 'delete');

        $this->assertDatabaseHas(new TaskApplication(), ['id' => $application->id]);

        $this->signIn();

        $this->validateCollaborateRole($route, 'delete');

        $this->assertDatabaseMissing(new TaskApplication(), ['id' => $application->id]);
    }

    /**
     * @return void
     */
    public function testTaskApplicationMailContent(): void
    {
        $task = Task::factory()->create(['user_id' => null]);
        $application = TaskApplication::factory()->create(['task_id' => $task->id]);

        $mail = new TaskApplicationCreatedEmail($application);

        $mail->assertSeeInHtml($task->title, false);
    }

    public function testItFiltersCorrectly(): void
    {
        $applications = TaskApplication::factory()
            ->count(4)
            ->create();

        $first = $applications->shift();

        $this->signIn($this->collaborateSuperUser());

        $response = $this->get(route('collaborate.task-applications.index'))
            ->assertSee($first->task->title);

        $reqCollection = RequirementsCollection::factory()->create();
        $taskType = TaskType::factory()->create();
        $applications->each(function ($appl) use ($response, $reqCollection, $taskType) {
            $response->assertSee($appl->task->title);
            $appl->task->update(['task_type_id' => $taskType->id]);
            $appl->task->document->expression->work->update(['primary_location_id' => $reqCollection->id]);
        });

        $response = $this->get(route('collaborate.task-applications.index', ['jurisdiction' => $first->task->document->expression->work->primary_location_id]))
            ->assertSee($first->task->title);
        $applications->each(fn ($appl) => $response->assertDontSee($appl->task->title));

        $response = $this->get(route('collaborate.task-applications.index', ['types' => [$first->task->task_type_id]]))
            ->assertSee($first->task->title);
        $applications->each(fn ($appl) => $response->assertDontSee($appl->task->title));
    }
}
