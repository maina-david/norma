<?php

namespace Tests\Feature\Workflows;

use App\Enums\Corpus\ReferenceType;
use App\Enums\Workflows\DependentUnitsOption;
use App\Enums\Workflows\ProjectType;
use App\Enums\Workflows\TaskStatus;
use App\Enums\Workflows\TaskTriggerType;
use App\Enums\Workflows\WizardStep;
use App\Models\Arachno\Source;
use App\Models\Auth\User;
use App\Models\Collaborators\Group;
use App\Models\Corpus\Pivots\LegalDomainReference;
use App\Models\Corpus\Pivots\LocationReference;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalDomainLegalUpdate;
use App\Models\Ontology\LegalDomain;
use App\Models\Requirements\ReferenceRequirementDraft;
use App\Models\Workflows\Board;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTrigger;
use App\Models\Workflows\TaskType;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TaskCreationControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testWorkflow(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var LegalDomain $domain */
        $domain = LegalDomain::factory()->create();
        /** @var Location $location */
        $location = Location::factory()->create();
        /** @var Group $group */
        $group = Group::factory()->create();
        /** @var TaskType $parentType */
        $parentType = TaskType::factory()->create(['is_parent' => true]);
        /** @var TaskType $child1 */
        $child1 = TaskType::factory()->create(['is_parent' => false]);
        /** @var TaskType $child2 */
        $child2 = TaskType::factory()->create(['is_parent' => false]);
        /** @var Board $board */
        $board = Board::factory()
            ->hasAttached($child1)
            ->hasAttached($child2)
            ->create([
                'parent_task_type_id' => $parentType->id,
                'task_type_order' => "{$child1->id},{$child2->id}",
                'wizard_steps' => [
                    WizardStep::CREATE_WORK->value,
                    WizardStep::CREATE_LEGAL_UPDATE->value,
                    WizardStep::CREATE_DOCUMENT->value,
                    WizardStep::SELECT_WORK->value,
                    WizardStep::SELECT_MONITOR_SOURCE->value,
                    WizardStep::SELECT_LEGAL_UPDATE->value,
                    WizardStep::ATTACH_DOMAINS->value,
                    WizardStep::ATTACH_LOCATIONS->value,
                    WizardStep::SELECT_PROJECT_TYPE->value,
                ],
                'task_type_defaults' => [
                    $parentType->id => [],
                    $child2->id => [
                        'depends_on' => $child1->id,
                        'set_current_units' => 1,
                        'set_current_units_trigger' => TaskStatus::inReview()->value,
                        'set_current_units_type' => DependentUnitsOption::STATEMENTS->value,
                    ],
                    $child1->id => [
                        'manager_id' => $user->id,
                        'move_dependent_task' => 1,
                        'move_dependent_task_trigger' => TaskStatus::done()->value,
                        'move_dependent_task_status' => TaskStatus::todo()->value,
                        'set_dependent_due_date' => 1,
                        'set_dependent_due_date_trigger' => TaskStatus::done()->value,
                        'set_dependent_task_duration' => 'PT1H',
                        'set_current_due_date' => 1,
                        'set_current_due_date_trigger' => TaskStatus::todo()->value,
                        'set_current_task_duration' => 'PT1H',
                        'set_current_units' => 1,
                        'set_current_units_trigger' => TaskStatus::inReview()->value,
                        'set_current_units_type' => DependentUnitsOption::REFERENCES->value,
                        'set_current_units_multiple' => 0.1,
                    ],
                ],
            ]);

        $this->signIn($this->collaborateSuperUser());

        $this->get(route('collaborate.tasks.wizard.create', ['board' => $board->id]))
            ->assertSuccessful()
            ->assertSeeSelector('//div[text()[contains(.,"Create Work")]]')
            ->assertSeeSelector('//div[text()[contains(.,"Attach Domains")]]')
            ->assertSeeSelector('//div[text()[contains(.,"Attach Locations")]]')
            ->assertSeeSelector('//div[text()[contains(.,"Group Assignment")]]')
            ->assertSeeSelector('//div[text()[contains(.,"Task Details")]]');

        $work = Work::factory()->raw(['primary_location_id' => $location->id]);
        $work['source_document'] = UploadedFile::fake()->image('work.png');
        $payload = $work;

        $this->from(route('collaborate.tasks.index'))
            ->get(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 1]))
            ->assertSuccessful();

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 1]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 2]));

        $update = LegalUpdate::factory()->raw(['primary_location_id' => $location->id]);
        $payload = $update;
        $payload['content_resource_file'] = UploadedFile::fake()->image('testing.png');
        $payload['domains'] = [$domain->id];

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 2]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 3]));

        $documentTitle = $this->faker->title;
        $payload = ['title' => $documentTitle];

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 3]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 4]));

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create();

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 4]), ['work_expression_id' => $expression->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 5]));

        $source = Source::factory()->create();

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 5]), ['source_id' => $source->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 6]));

        $selectUpdate = LegalUpdate::factory()->create();

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 6]), ['legal_update_id' => $selectUpdate->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 7]));

        $payload = ['legal_domains' => [$domain->id]];

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 7]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 8]));

        $payload = ['locations' => $location->id];

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 8]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 9]));

        $payload = ['project_type' => ProjectType::MAINTENANCE->value];

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 9]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 10]));

        $payload = ['group_id' => $group->id];

        $response = $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 10]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 11]));

        $this->followRedirects($response);

        $payload = [
            "type_{$parentType->id}_title" => $work['title'],
            "type_{$child1->id}_title" => $child1->name . ' - ' . $work['title'],
            "type_{$child2->id}_title" => $child2->name . ' - ' . $work['title'],
        ];

        $this->post(route('collaborate.tasks.wizard.store', ['board' => $board->id, 'stage' => 11]), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.tasks.index'));

        $this->assertDatabaseHas(Work::class, ['title' => $work['title']]);

        /** @var Work $work */
        $work = Work::where('title', $work['title'])->firstOrFail();

        $this->assertDatabaseHas(WorkExpression::class, ['work_id' => $work->id, 'id' => $work->active_work_expression_id]);

        $this->assertDatabaseMissing(WorkExpression::class, ['source_document_id' => null]);

        $this->assertDatabaseHas(Reference::class, ['work_id' => $work->id]);

        /** @var Reference $reference */
        $reference = Reference::where('work_id', $work->id)->firstOrFail();

        $this->assertDatabaseHas(LegalDomainReference::class, ['legal_domain_id' => $domain->id, 'reference_id' => $reference->id]);
        $this->assertDatabaseHas(LegalDomainLegalUpdate::class, ['legal_domain_id' => $domain->id, 'legal_update_id' => $selectUpdate->id]);

        $this->assertDatabaseHas(LocationReference::class, ['location_id' => $location->id, 'reference_id' => $reference->id]);

        $this->assertDatabaseHas(Document::class, ['work_id' => $work->id, 'title' => $work->title]);

        /** @var Document $document */
        $document = Document::where('work_id', $work->id)->firstOrFail();

        $this->assertDatabaseHas(Task::class, ['document_id' => $document->id]);

        $this->assertSame(3, Task::where('document_id', $document->id)->count());

        $this->assertDatabaseHas(TaskTrigger::class, ['triggered' => false, 'trigger_type' => TaskTriggerType::ON_STATUS_CHANGE->value]);

        $this->assertSame(5, TaskTrigger::where('trigger_type', TaskTriggerType::ON_STATUS_CHANGE->value)->count());

        Reference::factory()->count(5)->create(['work_id' => $work->id, 'type' => ReferenceType::citation()->value]);

        $task = Task::where('task_type_id', $child1->id)->where('document_id', $document->id)->first();

        $task->update(['task_status' => TaskStatus::inReview()->value]);

        $this->assertSame(5 * 0.1, $task->refresh()->units);

        Reference::typeCitation()
            ->where('work_id', $work->id)
            ->get()
            ->each(function ($reference) {
                ReferenceRequirementDraft::factory()->for($reference)->create();
            });

        $task = Task::where('task_type_id', $child2->id)->where('document_id', $document->id)->first();

        $task->update(['task_status' => TaskStatus::inReview()->value]);

        $this->assertSame(round(5 * 2 / 60, 2), $task->refresh()->units);
    }
}
