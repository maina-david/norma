<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Enums\Corpus\WorkStatus;
use App\Enums\Corpus\WorkType;
use App\Models\Actions\ActionArea;
use App\Models\Arachno\Source;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use HotwiredLaravel\TurboLaravel\Testing\AssertableTurboStream;
use Tests\Feature\Abstracts\CrudTestCase;

class WorkControllerTest extends CrudTestCase
{
    /** @var bool */
    protected bool $searchable = true;

    /** @var string */
    protected string $sortBy = 'title';

    protected bool $collaborate = true;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Work::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.works';
    }

    /**
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['title'];
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
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
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
        return [
            'create', 'store', 'destroy',
        ];
    }

    /**
     * @return void
     */
    public function testItFiltersCorrectly(): void
    {
        $works = Work::factory()
            ->state(['primary_location_id' => Location::factory()])
            ->count(4)
            ->create();

        $first = $works->shift();

        $location = $first->primary_location_id;

        $this->signIn($this->collaborateSuperUser());

        $response = $this->get(route('collaborate.works.index'))
            ->assertSee($first->title);

        $works->each(fn ($work) => $response->assertSee($work->title));

        $response = $this->get(route('collaborate.works.index', ['jurisdiction' => $location]))
            ->assertSee($first->title);

        $works->each(fn ($work) => $response->assertDontSee($work->title));

        $domain = LegalDomain::factory()->create();
        $question = ContextQuestion::factory()->create();
        $sai = AssessmentItem::factory()->create();
        $tag = Tag::factory()->create();
        $category = Category::factory()->create();
        $source = Source::factory()->create();
        $action = ActionArea::factory()->create();

        $reference = Reference::factory()->create(['work_id' => $first->id]);

        $reference->actionAreas()->attach($action->id);
        $reference->legalDomains()->attach($domain->id);
        $reference->contextQuestions()->attach($question->id);
        $reference->tags()->attach($tag->id);
        $reference->assessmentItems()->attach($sai->id);
        $reference->categories()->attach($category->id);
        $first->update(['source_id' => $source->id]);

        $response = $this->get(route('collaborate.works.index', ['domain' => 'all']))
            ->assertSee($first->title);

        $works->each(fn ($work) => $response->assertSee($work->title));

        $response = $this->get(route('collaborate.works.index', ['domains' => [$domain->id]]))
            ->assertSee($first->title);

        $works->each(fn ($work) => $response->assertDontSee($work->title));

        $first->update([
            'work_type' => WorkType::bill(),
            'status' => WorkStatus::pending(),
        ]);

        $response = $this->get(route('collaborate.works.index', ['workType' => WorkType::bill()->value]))
            ->assertSee($first->title);

        $works->each(fn ($work) => $response->assertDontSee($work->title));

        $response = $this->get(route('collaborate.works.index', ['status' => WorkStatus::pending()->value]))
            ->assertSee($first->title);

        $works->each(fn ($work) => $response->assertDontSee($work->title));

        $response = $this->get(route('collaborate.works.index', ['questions' => [$question->id]]))
            ->assertSee($first->title);
        $works->each(fn ($work) => $response->assertDontSee($work->title));
        $response = $this->get(route('collaborate.works.index', ['tags' => [$tag->id]]))
            ->assertSee($first->title);
        $works->each(fn ($work) => $response->assertDontSee($work->title));
        $response = $this->get(route('collaborate.works.index', ['topics' => [$category->id]]))
            ->assertSee($first->title);
        $works->each(fn ($work) => $response->assertDontSee($work->title));
        $response = $this->get(route('collaborate.works.index', ['assessment_items' => [$sai->id]]))
            ->assertSee($first->title);
        $works->each(fn ($work) => $response->assertDontSee($work->title));
        $response = $this->get(route('collaborate.works.index', ['source' => $source->id]))
            ->assertSee($first->title);
        $works->each(fn ($work) => $response->assertDontSee($work->title));
        $response = $this->get(route('collaborate.works.index', ['actionAreas' => [$action->id]]))
            ->assertSee($first->title);
        $works->each(fn ($work) => $response->assertDontSee($work->title));
    }

    /**
     * @return void
     */
    public function testItReturnsAStream(): void
    {
        $work = Work::factory()->create();

        $frame = "corpus_work_{$work->id}";

        $this->signIn($this->collaborateSuperUser());

        $this->get(route('collaborate.works.show', $work->id), ['turbo-frame' => $frame])
            ->assertSuccessful()
            ->assertTurboStream(function (AssertableTurboStream $streams) use ($frame) {
                return $streams->has(1)->hasTurboStream(function ($stream) use ($frame) {
                    return $stream->where('target', $frame)->where('action', 'update');
                });
            });
    }

    /**
     * @return void
     */
    public function testWorkingWithTasks(): void
    {
        $document = Document::factory()->create();
        $document->load(['expression.work']);
        $task = Task::factory()->create(['document_id' => $document->id]);

        $this->signIn($this->collaborateSuperUser());

        $this->get(route('collaborate.works.show', ['work' => $document->expression->work_id]))
            ->assertSee($document->expression->work->title)
            ->assertDontSee('workflow-task-header');

        $this->get(route('collaborate.works.task.show', ['work' => $document->expression->work_id, 'task' => $task->id]))
            ->assertSee($document->expression->work->title)
            ->assertSee('workflow-task-header');
    }

    /**
     * @return void
     */
    public function testLinkToDoc(): void
    {
        $this->signIn($this->collaborateSuperUser());

        /** @var CatalogueDoc */
        $catDoc = CatalogueDoc::factory()->create(['source_unique_id' => 'test']);
        $catDoc->setUid()->save();
        /** @var Work */
        $work = Work::factory()->create();

        $this->post(route('collaborate.corpus.works.link-to-doc', ['work' => $work]), [
            'catalogue_doc_id' => $catDoc->id,
        ])
            ->assertRedirect()
            ->assertSessionHas('flash.message', __('notifications.success'));
        $this->assertSame($work->refresh()->uid, $catDoc->uid);

        // trying to link again should result in an error, as the doc is already linked
        $this->post(route('collaborate.corpus.works.link-to-doc', ['work' => $work]), [
            'catalogue_doc_id' => $catDoc->id,
        ])
            ->assertRedirect()
            ->assertSessionHas('flash.type', 'error');

        $this->delete(route('collaborate.corpus.works.unlink-from-doc', ['work' => $work]))
            ->assertRedirect()
            ->assertSessionHas('flash.message', __('notifications.success'));
        $this->assertNull($work->refresh()->uid);
    }
}
