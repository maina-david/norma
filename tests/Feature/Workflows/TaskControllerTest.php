<?php

namespace Tests\Feature\Workflows;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Enums\Corpus\WorkStatus;
use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Enums\Workflows\AutoArchiveOption;
use App\Enums\Workflows\ComputedTaskAction;
use App\Enums\Workflows\OnCompleteValidationType;
use App\Enums\Workflows\OnToDoValidationType;
use App\Enums\Workflows\PaymentStatus;
use App\Enums\Workflows\RelativeTaskAction;
use App\Enums\Workflows\TaskComplexity;
use App\Enums\Workflows\TaskPriority;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTypeOption;
use App\Enums\Workflows\TaskValidationType;
use App\Http\ResourceActions\Workflows\ArchiveTasks;
use App\Http\ResourceActions\Workflows\ChangeAssignee;
use App\Http\ResourceActions\Workflows\ChangeGroup;
use App\Http\ResourceActions\Workflows\ChangeManager;
use App\Http\ResourceActions\Workflows\ChangePriority;
use App\Http\ResourceActions\Workflows\ChangeStatus;
use App\Http\ResourceActions\Workflows\RequestPayment;
use App\Http\Services\Workflows\Tasks\UserTaskFilters;
use App\Models\Assess\AssessmentItem;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\Group;
use App\Models\Corpus\Pivots\LegalDomainReferenceDraft;
use App\Models\Corpus\Pivots\LocationReferenceDraft;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\LegalDomain;
use App\Models\Payments\PaymentRequest;
use App\Models\Requirements\ReferenceRequirementDraft;
use App\Models\Requirements\ReferenceSummaryDraft;
use App\Models\Requirements\Summary;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\Project;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskCheck;
use App\Models\Workflows\TaskTransition;
use App\Models\Workflows\TaskType;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Abstracts\CrudTestCase;

class TaskControllerTest extends CrudTestCase
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Task::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.tasks';
    }

    /**
     * Perform tasks before creation of the test model.
     *
     * @param Factory $factory
     * @param string  $route
     *
     * @return Factory
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        return $factory->state([
            'task_status' => TaskStatus::pending(),
        ]);
    }

    /**
     * Get payload for the base resource route.
     *
     * @param array $params
     *
     * @return array
     */
    protected static function resourceRouteParams(array $params): array
    {
        if (!$board = Board::first()) {
            $board = Board::factory()->create();
        }

        return ['workflow' => $board->id, 'status' => [TaskStatus::pending()->value]];
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return [];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return [];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array
     */
    protected static function excludeActions(): array
    {
        return ['create', 'store', 'destroy'];
    }

    /**
     * @return void
     */
    public function testTestRedirect(): void
    {
        $board = Board::factory()->create();

        $this->signIn($this->collaborateSuperUser());

        $this->get(route('collaborate.tasks.index'))
            ->assertRedirect(route('collaborate.tasks.index', ['workflow' => $board->id]));
    }

    /**
     * @return void
     */
    public function testItRendersTaskForTaskTypesPerBoard(): void
    {
        $user = $this->signIn($this->collaborateSuperUser());

        $board = Board::factory()->create();
        $taskTypes = TaskType::factory()->hasAttached($board)->count(3)->create();
        $type = $taskTypes->first();
        $tasks = Task::factory()->count(30)->create(['task_type_id' => $type->id]);
        $board->task_type_order = $taskTypes->map->id->join(',');
        $board->save();

        $this->assertDatabaseHas(TaskTransition::class, ['task_id' => $tasks->first()->id, 'transitioned_field' => 'task_id']);

        $user->updateSetting(UserTaskFilters::USER_SETTING_KEY, [
            'boardFilter' => $board->id,
            'taskTypeFilter' => $taskTypes->pluck('id')->toArray(),
        ]);

        // fetches the settings from the database and redirects as expected.
        $this->get(route('collaborate.tasks.index'))
            ->assertRedirect(route('collaborate.tasks.index', ['types' => $taskTypes->pluck('id')->toArray()]));

        $response = $this->withSession(['workflow_filters' => ['types' => $taskTypes->pluck('id')->toArray()]])
            ->get(route('collaborate.tasks.index', ['workflow' => $board->id]))
            ->assertSuccessful()
            ->assertSeeSelector('//turbo-frame')
            ->assertSee('turbo-frame');

        $taskTypes->each(function ($type) use ($response) {
            $response
                ->assertSeeSelector(sprintf('//turbo-frame[@id="task-type-tasks-%d"]', $type->id))
                ->assertSeeSelector(sprintf('//turbo-frame[@id="task-type-tasks-%d-pagination"]', $type->id));
        });

        $frame = "task-type-tasks-{$type->id}";

        $this->withExceptionHandling()
            ->get(route('collaborate.task-type.tasks.index', ['type' => $type->id]))
            ->assertNotFound();

        $this->get(route('collaborate.task-type.tasks.index', ['type' => $type->id, 'sort' => 'id']), ['turbo-frame' => $frame])
            ->assertSuccessful()
            ->assertSee($frame)
            ->assertSee("{$frame}-pagination")
            ->assertSee('More')
            ->assertTurboStream(function (AssertableTurboStream $streams) {
                return $streams->has(2);
            });
    }

    /**
     * @return void
     */
    public function testItFiltersCorrectly(): void
    {
        $board = Board::factory()->create();
        $taskTypes = TaskType::factory()->hasAttached($board)->count(3)->create();
        $type = $taskTypes->first();
        $tasks = Task::factory()->count(2)->create(['task_type_id' => $type->id]);
        $board->task_type_order = $taskTypes->map->id->join(',');
        $board->save();

        $assignee = User::factory()->create();
        $manager = User::factory()->create();
        $author = User::factory()->create();

        $location = Location::factory()->create();
        $work = Work::factory()->create(['primary_location_id' => $location->id]);
        $document = Document::create(['work_id' => $work->id]);
        $domain = LegalDomain::factory()->create();
        $project = Project::factory()->create();
        Reference::factory()
            ->hasAttached($location, [], 'locations')
            ->hasAttached($domain, [], 'legalDomains')
            ->create(['work_id' => $work->id]);

        $filtered = Task::factory()->count(2)->create([
            'document_id' => $document->id,
            'task_type_id' => $type->id,
            'task_status' => TaskStatus::pending(),
            'priority' => TaskPriority::high(),
            'user_id' => $assignee->id,
            'author_id' => $author->id,
            'manager_id' => $manager->id,
            'project_id' => $project->id,
        ]);

        $filters = [
            'type' => $type->id,
            'types' => [$type->id],
            'status' => [TaskStatus::pending()->value],
            'priority' => [TaskPriority::high()->value],
            'assignee' => $assignee->id,
            'author' => $author->id,
            'manager' => $manager->id,
            'jurisdiction' => $location->id,
            'domains' => $domain->id,
            'project' => $project->id,
        ];

        $this->signIn($this->collaborateSuperUser());

        $frame = "task-type-tasks-{$type->id}";

        $response = $this->get(route('collaborate.task-type.tasks.index', ['type' => $type->id]), ['turbo-frame' => $frame])
            ->assertSuccessful()
            ->assertSee($frame)
            ->assertSee('append');

        $filtered->each(fn ($task) => $response->assertSee($task->title));
        $filtered->each(fn ($task) => $response->assertSee($task->title));

        $response->assertTurboStream(function (AssertableTurboStream $streams) {
            return $streams->has(2);
        });

        $response = $this->get(route('collaborate.task-type.tasks.index', $filters), ['turbo-frame' => $frame])
            ->assertSuccessful()
            ->assertSee($frame)
            ->assertSee('append');

        $tasks->each(fn ($task) => $response->assertDontSee($task->title));
        $filtered->each(fn ($task) => $response->assertSee($task->title));

        $response->assertTurboStream(function (AssertableTurboStream $streams) {
            return $streams->has(2);
        });

        $filters['search'] = $filtered->first()->title;

        $response = $this->get(route('collaborate.task-type.tasks.index', $filters), ['turbo-frame' => $frame])
            ->assertSuccessful()
            ->assertSee($frame)
            ->assertSee('append');

        $tasks->each(fn ($task) => $response->assertDontSee($task->title));

        $filtered->each(function ($task, $index) use ($response) {
            if ($index !== 0) {
                $response->assertDontSee($task->title);
            }
        });

        $response->assertSee($filtered->first()->title);

        $response->assertTurboStream(function (AssertableTurboStream $streams) {
            return $streams->has(2);
        });
    }

    /**
     * @return void
     */
    public function testItShowsTheTaskDetails(): void
    {
        $task = Task::factory()->create(['task_status' => TaskStatus::pending()]);

        $permissions = ['workflows.task.viewAny'];

        $role = Role::factory()->collaborate()->create(['permissions' => $permissions]);
        $user = User::factory()->hasAttached($role)->create();

        $this->signIn($user);

        $this->withExceptionHandling()
            ->get(route('collaborate.tasks.show', $task), ['turbo-frame' => 'frame'])
            ->assertNotFound();

        $role->refresh()->update([
            'permissions' => [...$role->permissions, 'workflows.task.view-pending'],
        ]);

        $user->flushComputedPermissions();

        $this->get(route('collaborate.tasks.show', $task))
            ->assertSuccessful()
            ->assertSeeSelector('//div[text()[contains(.,"Pending")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"ToDo")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Progress")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Review")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Done")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Archive")]]');

        $this->get(route('collaborate.tasks.show', $task), ['turbo-frame' => 'test-frame'])
            ->assertSuccessful()
            ->assertSeeSelector('//div[text()[contains(.,"Pending")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"ToDo")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Progress")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Review")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Done")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Archive")]]');

        $task->user_id = $user->id;
        $task->save();

        $this->get(route('collaborate.tasks.show', $task))
            ->assertSuccessful()
            ->assertSeeSelector('//div[text()[contains(.,"Pending")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"ToDo")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Progress")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Review")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Done")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Archive")]]');

        $this->post(route('collaborate.tasks.status', ['task' => $task, 'status' => TaskStatus::todo()->value]))
            ->assertForbidden();

        $task->task_status = TaskStatus::todo();
        $task->save();

        $this->get(route('collaborate.tasks.show', $task))
            ->assertSuccessful()
            ->assertDontSeeSelector('//button[text()[contains(.,"Pending")]]')
            ->assertSeeSelector('//a[text()[contains(.,"ToDo")]]')
            ->assertSeeSelector('//button[text()[contains(.,"In Progress")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Review")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Done")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Archive")]]');

        $this->post(route('collaborate.tasks.status', ['task' => $task, 'status' => TaskStatus::inProgress()->value]))
            ->assertRedirect();

        $this->assertTrue(TaskStatus::inProgress()->is($task->refresh()->task_status));

        $this->get(route('collaborate.tasks.show', $task))
            ->assertSuccessful()
            ->assertDontSeeSelector('//button[text()[contains(.,"Pending")]]')
            ->assertSeeSelector('//button[text()[contains(.,"ToDo")]]')
            ->assertSeeSelector('//a[text()[contains(.,"In Progress")]]')
            ->assertSeeSelector('//button[text()[contains(.,"In Review")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Done")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Archive")]]');

        $this->post(route('collaborate.tasks.status', ['task' => $task, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect();

        $this->assertTrue(TaskStatus::inReview()->is($task->refresh()->task_status));

        $this->post(route('collaborate.tasks.status', ['task' => $task, 'status' => 500]))
            ->assertNotFound();

        $this->post(route('collaborate.tasks.status', ['task' => $task, 'status' => TaskStatus::done()->value]))
            ->assertForbidden();

        $this->get(route('collaborate.tasks.show', $task))
            ->assertSuccessful()
            ->assertDontSeeSelector('//button[text()[contains(.,"Pending")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"ToDo")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"In Progress")]]')
            ->assertSeeSelector('//div[text()[contains(.,"In Review")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Done")]]')
            ->assertDontSeeSelector('//button[text()[contains(.,"Archive")]]')
            ->assertSeeSelector(sprintf('//turbo-frame[@id="comments_workflows_task_%d"]', $task->id));
    }

    /**
     * @return void
     */
    public function testCanBulkArchive(): void
    {
        $tasks = Task::factory()->count(10)->create(['task_status' => TaskStatus::pending()]);

        $this->signIn($this->collaborateSuperUser());

        $payload = collect();

        $tasks->each(function ($item) use ($payload) {
            $payload->put("actions-checkbox-{$item->id}", true);
            $this->assertTrue(TaskStatus::pending()->is($item->task_status));
        });

        $payload = $payload->toArray();
        $payload['action'] = (new ArchiveTasks())->actionId();

        $this->post(route('collaborate.tasks.actions'), $payload)
            ->assertRedirect();

        $tasks->each(fn ($task) => $this->assertDatabaseHas(Task::class, ['id' => $task->id, 'task_status' => TaskStatus::archive()->value]));
    }

    public function testApplicationOfUnitsAndComplexity(): void
    {
        /** @var TaskType $type */
        $type = TaskType::factory()->create();

        $defaults = [
            'set_dependent_complexity' => RelativeTaskAction::OWN->value,
            'set_dependent_units_type' => ComputedTaskAction::NOT_AID_STATEMENTS_3MIN,
        ];

        /** @var Board $board */
        $board = Board::factory()->create(['task_type_defaults' => [$type->id => $defaults]]);

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create();
        /** @var Document $document */
        $document = Document::factory()->create(['work_expression_id' => $expression->id]);
        /** @var Task $task */
        $task = Task::factory()->create([
            'document_id' => $document->id,
            'board_id' => $board->id,
            'task_type_id' => $type->id,
            'task_status' => TaskStatus::inProgress(),
            'complexity' => null,
        ]);

        /** @var Collection<Task> $children */
        $children = Task::factory()
            ->count(3)
            ->create([
                'depends_on_task_id' => $task->id,
                'document_id' => $document->id,
                'board_id' => $board->id,
                'task_status' => TaskStatus::inProgress(),
                'units' => null,
                'complexity' => null,
            ]);

        $task->update(['task_status' => TaskStatus::inProgress(), 'complexity' => TaskComplexity::level4()->value]);

        $children->each(fn ($child) => $this->assertTrue(TaskComplexity::level4()->is($child->refresh()->complexity)));

        $references = Reference::factory()->count(3)->create([
            'work_id' => $expression->work_id,
            'type' => ReferenceType::citation()->value,
        ]);

        $children->each(fn ($child) => $child->update(['units' => null]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertSame(9 / 60, $child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => ['set_dependent_units_type' => ComputedTaskAction::NOT_AID_STATEMENTS_4MIN],
            ],
        ]);

        $task->update(['task_status' => TaskStatus::inProgress()]);

        $children->each(fn ($child) => $child->update(['units' => null]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertSame(12 / 60, $child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => ['set_dependent_units_type' => ComputedTaskAction::STATEMENTS],
            ],
        ]);

        ReferenceRequirementDraft::factory()->for($references->first())->create();

        $task->update(['task_status' => TaskStatus::inProgress()]);

        $children->each(fn ($child) => $child->update(['units' => null]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertSame(round(2 / 60, 2), $child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => ['set_dependent_units_type' => ComputedTaskAction::SUMMARIES],
            ],
        ]);

        $summary = Summary::factory()->create([
            'reference_id' => $references->first()->id,
        ]);
        $summaryDraft = ReferenceSummaryDraft::factory()->for($references->first())->create();

        $task->update(['task_status' => TaskStatus::inProgress()]);

        $children->each(fn ($child) => $child->update(['units' => null, 'complexity' => TaskComplexity::level5()->value]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertSame(TaskComplexity::level5()->hours(), $child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => ['set_dependent_units_type' => ComputedTaskAction::SUMMARIES_AND_STATEMENTS],
            ],
        ]);

        $task->update(['task_status' => TaskStatus::inProgress()]);

        $children->each(fn ($child) => $child->update(['units' => null, 'complexity' => TaskComplexity::level5()->value]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $summaryUnit = TaskComplexity::level5()->hours();
        $statementUnit = round(2 / 60, 2);

        $children->each(fn ($child) => $this->assertSame($summaryUnit + $statementUnit, $child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => ['set_dependent_units_type' => RelativeTaskAction::IGNORE],
            ],
        ]);

        $task->update(['task_status' => TaskStatus::inProgress()]);

        $children->each(fn ($child) => $child->refresh()->update(['units' => null]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => [
                    'set_dependent_units_type' => RelativeTaskAction::MULTIPLE_OF,
                    'set_dependent_units_multiple' => 0.25,
                ],
            ],
        ]);

        $task->update(['task_status' => TaskStatus::inProgress(), 'units' => 2]);

        $children->each(fn ($child) => $child->refresh()->update(['units' => null]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertSame(2 * 0.25, $child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => [
                    'set_dependent_units_type' => RelativeTaskAction::CUSTOM_VALUE,
                    'set_dependent_units_multiple' => 0.5,
                ],
            ],
        ]);

        $task->update(['task_status' => TaskStatus::inProgress(), 'units' => 2]);

        $children->each(fn ($child) => $child->refresh()->update(['units' => null]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertSame(0.5, $child->refresh()->units));

        $board->update([
            'task_type_defaults' => [
                $type->id => [
                    'set_dependent_units_type' => RelativeTaskAction::OWN,
                    'set_dependent_units_multiple' => 0.5,
                ],
            ],
        ]);

        $task->update(['task_status' => TaskStatus::inProgress(), 'units' => 2]);

        $children->each(fn ($child) => $child->refresh()->update(['units' => null]));

        $children->each(fn ($child) => $this->assertNull($child->refresh()->units));

        $task->update(['task_status' => TaskStatus::done()]);

        $children->each(fn ($child) => $this->assertSame(2.0, $child->refresh()->units));
    }

    public function testTaskValidators(): void
    {
        /** @var TaskType $type */
        $type = TaskType::factory()->create();

        $nextType = TaskType::factory()->create();

        $typeDefaults = [
            $type->id => [
                'validations' => [
                    TaskValidationType::COMPLETE->value => [
                        OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                        OnCompleteValidationType::WORK_EXPRESSION->value => TaskTypeOption::APPLIED->value,
                    ],
                ],
            ],
            $nextType->id => [],
        ];
        /** @var Board $board */
        $board = Board::factory()->create(['task_type_order' => "{$type->id},{$nextType->id}", 'task_type_defaults' => $typeDefaults]);

        /** @var Work $work */
        $work = Work::factory()->create(['status' => WorkStatus::pending()->value]);
        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create(['work_id' => $work->id]);
        /** @var Document $document */
        $document = Document::factory()->create(['work_expression_id' => $expression->id]);

        $parent = Task::factory()->create();
        /** @var Task $task */
        $task = Task::factory()->create(['parent_task_id' => $parent->id, 'document_id' => $document->id, 'task_type_id' => $type->id, 'task_status' => TaskStatus::inProgress(), 'board_id' => $board->id]);

        $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $work->refresh()->update(['status' => WorkStatus::active()]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $work->refresh()->update(['active_work_expression_id' => $expression->id]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $reference = Reference::factory()->create(['work_id' => $work->id]);
        $summary = Summary::factory()->create(['reference_id' => $reference->id]);
        $summaryDraft = ReferenceSummaryDraft::factory()->create(['reference_id' => $reference->id]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::COMPLETE->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $summaryDraft->refresh()->update(['summary_body' => $this->faker->paragraph()]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::APPLIED->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $summaryDraft->delete();

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);
        ReferenceRequirementDraft::factory()->for($reference)->create();

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::STATEMENTS->value => TaskTypeOption::APPLIED->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        ReferenceRequirementDraft::find($reference->id)->delete();

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);
        $reference->refresh()->update(['status' => ReferenceStatus::pending()]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::STATEMENTS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::CITATIONS->value => TaskTypeOption::APPLIED->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $reference->refresh()->update(['status' => ReferenceStatus::active()]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);
        $assessment = AssessmentItem::factory()->create();
        /** @var Reference $reference */
        $reference->assessmentItemDrafts()->create(['assessment_item_id' => $assessment->id, 'change_status' => 1]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::STATEMENTS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::CITATIONS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::METADATA->value => TaskTypeOption::APPLIED->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $reference->assessmentItemDrafts()->delete();

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        // test citation identification

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);
        /** @var Reference $reference */
        $reference->contentDraft()->create(['title' => 'Testing', 'html_content' => 'Testing content']);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::STATEMENTS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::CITATIONS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::METADATA->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::IDENTIFIED_CITATIONS->value => TaskTypeOption::APPLIED->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $reference->contentDraft()->delete();

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        // end test citation identification

        /**************************************************************************************************************
         * Legal Domains
         *************************************************************************************************************/

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::LEGAL_DOMAINS->value => TaskTypeOption::COMPLETE->value,
            ],
        ];

        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $domain = LegalDomain::factory()->create();
        $workReferences = Reference::where('work_id', $work->id)->pluck('id')->all();
        LegalDomainReferenceDraft::insert(collect($workReferences)->map(fn ($item) => ['legal_domain_id' => $domain->id, 'reference_id' => $item])->all());

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::LEGAL_DOMAINS->value => TaskTypeOption::APPLIED->value,
            ],
        ];

        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        LegalDomainReferenceDraft::whereIn('reference_id', $workReferences)->delete();
        $domain->references()->attach($workReferences);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        /**************************************************************************************************************
         * Locations
         *************************************************************************************************************/

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::LOCATIONS->value => TaskTypeOption::COMPLETE->value,
            ],
        ];

        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $location = Location::factory()->create();
        LocationReferenceDraft::insert(collect($workReferences)->map(fn ($item) => ['location_id' => $location->id, 'reference_id' => $item])->all());

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::LOCATIONS->value => TaskTypeOption::APPLIED->value,
            ],
        ];

        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        LocationReferenceDraft::whereIn('reference_id', $workReferences)->delete();
        $location->references()->attach($workReferences);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        /**************************************************************************************************************
         * Complexity
         *************************************************************************************************************/

        $task->refresh()->update(['task_status' => TaskStatus::inProgress()]);
        /** @var Task $sibling */
        $sibling = Task::factory()->create(['parent_task_id' => $parent->id, 'document_id' => $document->id, 'task_type_id' => $nextType->id, 'task_status' => TaskStatus::inProgress(), 'board_id' => $board->id, 'complexity' => null]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::STATEMENTS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::CITATIONS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::METADATA->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::COMPLEXITY->value => TaskTypeOption::APPLIED->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $sibling->refresh()->update(['complexity' => TaskComplexity::level5()->value]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $update = LegalUpdate::factory()->create(['highlights' => null, 'update_report' => null]);
        $document->update(['legal_update_id' => $update->id]);

        $sibling->refresh()->update(['task_status' => TaskStatus::inProgress(), 'document_id' => $document->id]);

        $typeDefaults[$nextType->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK_HIGHLIGHTS->value => TaskTypeOption::COMPLETE->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $sibling->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $update->refresh()->update(['highlights' => $this->faker->paragraph()]);

        $this->post(route('collaborate.tasks.status', ['task' => $sibling->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $sibling->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $typeDefaults[$nextType->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK_HIGHLIGHTS->value => TaskTypeOption::COMPLETE->value,
                OnCompleteValidationType::WORK_UPDATE_REPORT->value => TaskTypeOption::COMPLETE->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $sibling->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $update->refresh()->update(['update_report' => $this->faker->paragraph()]);

        $this->post(route('collaborate.tasks.status', ['task' => $sibling->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $sibling->refresh()->update(['task_status' => TaskStatus::inProgress()]);

        $typeDefaults[$nextType->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK_HIGHLIGHTS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::WORK_UPDATE_REPORT->value => TaskTypeOption::COMPLETE->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $sibling->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasErrors('new_task_status');

        $update->refresh()->update(['status' => LegalUpdatePublishedStatus::PUBLISHED->value]);

        $this->post(route('collaborate.tasks.status', ['task' => $sibling->id, 'status' => TaskStatus::inReview()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $task->refresh()->update(['task_status' => TaskStatus::pending()]);
        $dependent = Task::factory()->create(['depends_on_task_id' => $task->id, 'task_status' => TaskStatus::pending()->value]);

        $typeDefaults[$type->id]['validations'] = [
            TaskValidationType::COMPLETE->value => [
                OnCompleteValidationType::WORK->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::SUMMARIES->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::STATEMENTS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::CITATIONS->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::METADATA->value => TaskTypeOption::APPLIED->value,
                OnCompleteValidationType::COMPLEXITY->value => TaskTypeOption::APPLIED->value,
            ],
            TaskValidationType::TODO->value => [
                OnToDoValidationType::AUTO_ARCHIVE->value => AutoArchiveOption::NO_PENDING_SUMMARIES->value,
            ],
        ];
        $board->refresh()->update(['task_type_defaults' => $typeDefaults]);

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::todo()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertTrue(TaskStatus::archive()->is($task->refresh()->task_status));
        $this->assertTrue(TaskStatus::todo()->is($dependent->refresh()->task_status));

        $this->post(route('collaborate.tasks.status', ['task' => $task->id, 'status' => TaskStatus::done()->value]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    /**
     * @return void
     */
    public function testTestBulkActions(): void
    {
        $type = TaskType::factory()->create();
        $group = Group::factory()->create();

        /** @var Collaborator $user */
        $user = Collaborator::factory()->create();
        $user->profile->team->rates()->create([
            'for_task_type_id' => $type->id,
            'rate_per_unit' => 20,
            'unit_type_singular' => 'Hour',
            'unit_type_plural' => 'Hours',
        ]);
        $tasks = Task::factory()->count(10)->create([
            'task_type_id' => $type->id,
            'user_id' => null,
            'manager_id' => null,
            'priority' => TaskPriority::medium()->value,
            'task_status' => TaskStatus::pending()->value,
            'units' => 0.5,
        ]);

        $this->signIn($this->collaborateSuperUser());

        $payload = collect();

        $tasks->each(function ($item) use ($group, $payload) {
            $payload->put("actions-checkbox-{$item->id}", true);
            $this->assertEquals(TaskPriority::medium()->value, $item->priority);
            $this->assertEquals(TaskStatus::pending()->value, $item->task_status);
            $this->assertNotEquals($group->id, $item->group_id);
            $this->assertNull($item->user_id);
            $this->assertNull($item->manager_id);
        });

        $payload = $payload->toArray();
        $payload['action'] = (new ChangeAssignee())->actionId();

        $this->post(route('collaborate.tasks.actions'), $payload)->assertSeeSelector('//form//select[@name="user_id"]');

        $payload['action-validated'] = Hash::make(1);
        $payload['user_id'] = $user->id;

        $this->post(route('collaborate.tasks.actions'), $payload)->assertRedirect();
        $tasks->each(fn ($task) => $this->assertDatabaseHas(Task::class, ['id' => $task->id, 'user_id' => $user->id]));

        $payload['action-validated'] = null;
        $payload['action'] = (new ChangeGroup())->actionId();

        $this->post(route('collaborate.tasks.actions'), $payload)->assertSeeSelector('//form//select[@name="group_id"]');

        $payload['action-validated'] = Hash::make(1);
        $payload['group_id'] = $group->id;

        $this->post(route('collaborate.tasks.actions'), $payload)->assertRedirect();
        $tasks->each(fn ($task) => $this->assertDatabaseHas(Task::class, ['id' => $task->id, 'group_id' => $group->id]));

        $payload['action-validated'] = null;
        $payload['action'] = (new ChangeManager())->actionId();

        $this->post(route('collaborate.tasks.actions'), $payload)->assertSeeSelector('//form//select[@name="manager_id"]');

        $payload['action-validated'] = Hash::make(1);
        $payload['manager_id'] = $user->id;

        $this->post(route('collaborate.tasks.actions'), $payload)->assertRedirect();
        $tasks->each(fn ($task) => $this->assertDatabaseHas(Task::class, ['id' => $task->id, 'manager_id' => $user->id]));

        $payload['action-validated'] = null;
        $payload['action'] = (new ChangePriority())->actionId();

        $this->post(route('collaborate.tasks.actions'), $payload)->assertSeeSelector('//form//select[@name="priority"]');

        $payload['action-validated'] = Hash::make(1);
        $payload['priority'] = TaskPriority::immediate()->value;

        $this->post(route('collaborate.tasks.actions'), $payload)->assertRedirect();
        $tasks->each(fn ($task) => $this->assertDatabaseHas(Task::class, ['id' => $task->id, 'priority' => TaskPriority::immediate()->value]));

        $payload['action-validated'] = null;
        $payload['action'] = (new ChangeStatus())->actionId();

        $this->post(route('collaborate.tasks.actions'), $payload)->assertSeeSelector('//form//select[@name="task_status"]');

        $payload['action-validated'] = Hash::make(1);
        $payload['task_status'] = TaskStatus::done()->value;

        $this->post(route('collaborate.tasks.actions'), $payload)->assertRedirect();
        $tasks->each(fn ($task) => $this->assertDatabaseHas(Task::class, ['id' => $task->id, 'task_status' => TaskStatus::done()->value]));

        $tasks->each(fn ($task) => $this->assertDatabaseMissing(PaymentRequest::class, ['task_id' => $task->id]));

        $payload['action'] = (new RequestPayment())->actionId();
        $this->post(route('collaborate.tasks.actions'), $payload)->assertRedirect();

        $tasks->each(fn ($task) => $this->assertDatabaseHas(PaymentRequest::class, ['task_id' => $task->id]));
    }

    public function testCheckingTask(): void
    {
        $user = $this->signIn($this->collaborateSuperUser());
        $task = Task::factory()->create();
        $depends = Task::factory()->create(['depends_on_task_id' => $task->id]);

        $this->assertDatabaseMissing(TaskCheck::class, ['task_id' => $task->id, 'user_id' => $user->id]);

        $this->withoutExceptionHandling()
            ->post(route('collaborate.tasks.check', ['task' => $task->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(TaskCheck::class, ['task_id' => $task->id, 'user_id' => $user->id]);
    }

    public function testFilteringByPaymentStatus(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $board = Board::factory()->create();
        $type = TaskType::factory()->hasAttached($board)->create();
        $board->task_type_order = $type->id;
        $board->save();

        $paid = Task::factory()->create(['board_id' => $board->id, 'task_type_id' => $type->id]);
        $notPaid = Task::factory()->create(['board_id' => $board->id, 'task_type_id' => $type->id]);
        $notGenerated = Task::factory()->create(['board_id' => $board->id, 'task_type_id' => $type->id]);

        PaymentRequest::factory()->create(['task_id' => $paid->id, 'paid' => true]);
        PaymentRequest::factory()->create(['task_id' => $notPaid->id, 'paid' => false]);

        $this->turboGet(route('collaborate.task-type.tasks.index', ['type' => $type->id, 'workflow' => $board->id]))
            ->assertSuccessful()
            ->assertSee($paid->title)
            ->assertSee($notPaid->title)
            ->assertSee($notGenerated->title);

        $this->turboGet(route('collaborate.task-type.tasks.index', ['type' => $type->id, 'workflow' => $board->id, 'payment_status' => PaymentStatus::NONE->value]))
            ->assertSuccessful()
            ->assertDontSee($paid->title)
            ->assertDontSee($notPaid->title)
            ->assertSee($notGenerated->title);

        $this->turboGet(route('collaborate.task-type.tasks.index', ['type' => $type->id, 'workflow' => $board->id, 'payment_status' => PaymentStatus::PAID->value]))
            ->assertSuccessful()
            ->assertSee($paid->title)
            ->assertDontSee($notPaid->title)
            ->assertDontSee($notGenerated->title);

        $this->turboGet(route('collaborate.task-type.tasks.index', ['type' => $type->id, 'workflow' => $board->id, 'payment_status' => PaymentStatus::REQUESTED->value]))
            ->assertSuccessful()
            ->assertDontSee($paid->title)
            ->assertSee($notPaid->title)
            ->assertDontSee($notGenerated->title);
    }
}
