<?php

namespace Tests\Feature\Corpus\My;

use App\Enums\Auth\UserActivityType;
use App\Enums\Ontology\CategoryType;
use App\Models\Auth\UserActivity;
use App\Models\Compilation\RequirementsCollection;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\Tag;
use App\Services\Corpus\VolumeHighlighter;
use App\Services\Customer\ActiveNormasManager;
use App\Services\Search\Elastic\DocumentSearch;
use App\Services\Storage\WorkStorageProcessor;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PDF;
use Tests\Feature\My\MyTestCase;

class WorkControllerTest extends MyTestCase
{
    // /**
    //  * @return void
    //  */
    // public function testRequirements(): void
    // {
    //     [$user, $norma, $org] = $this->initUserNormaOrg();
    //     $routeName = 'my.corpus.requirements.index';
    //     $route = route($routeName);

    //     $work = Work::factory()->create();
    //     $work2 = Work::factory()->create();
    //     $reference = Reference::factory()->for($work)->create();
    //     $referenc2 = Reference::factory()->for($work2)->create();
    //     $work3 = Work::factory()->create();
    //     $reference3 = Reference::factory()->for($work3)->create();
    //     $reference->normas()->attach($norma);
    //     $referenc2->normas()->attach($norma);

    //     $location = Location::factory()->create();
    //     $location->references()->attach([$reference->id]);
    //     $norma->update(['location_id' => $location->id]);

    //     $location2 = Location::factory()->create();
    //     $location2->references()->attach([$referenc2->id]);

    //     $response = $this->get($route)->assertSuccessful();
    //     $response->assertSee($work->title);
    //     $response->assertSee($work2->title);
    //     $response->assertDontSee($work3->title);

    //     $tag = Tag::factory()->create();
    //     $reference->tags()->attach($tag);
    //     $response = $this->get(route($routeName, ['tags' => [$tag->id]]))->assertSuccessful();
    //     $response->assertSee($work->title);
    //     $response->assertDontSee($work2->title);
    //     $activities = UserActivity::all();
    //     $this->assertTrue($activities->where('activity_type', UserActivityType::viewedTag()->value)->isNotEmpty());
    //     $this->assertTrue($activities->where('activity_type', UserActivityType::viewedRequirements()->value)->isNotEmpty());

    //     $domain = LegalDomain::factory()->create();
    //     $reference->legalDomains()->attach($domain);
    //     $response = $this->get(route($routeName, ['domains' => [$domain->id]]))->assertSuccessful();
    //     $response->assertSee($work->title);
    //     $response->assertDontSee($work2->title);

    //     $response = $this->get(route($routeName, ['jurisdictionTypes' => [$location->location_type_id]]))->assertSuccessful();
    //     $response->assertSee($work->title);
    //     $response->assertDontSee($work2->title);
    // }

    // public function testRequirementsForOrg(): void
    // {
    //     [$user, $norma, $org] = $this->initUserNormaOrg();
    //     $norma2 = Norma::factory()->for($org)->create();
    //     $route = route('my.corpus.requirements.index');

    //     app(ActiveNormasManager::class)->activateAll($user);

    //     $work = Work::factory()->create();
    //     $reference = Reference::factory()->for($work)->create();
    //     $work2 = Work::factory()->create();
    //     $reference2 = Reference::factory()->for($work2)->create();
    //     $reference->normas()->attach($norma);
    //     $reference2->normas()->attach($norma2);

    //     $response = $this->get($route)->assertSuccessful();
    //     $response->assertSee($work->title);
    //     $response->assertSee($work2->title);

    //     $location = Location::factory()->create();
    //     $reference->locations()->attach($location);

    //     $jurisRoute = route('my.corpus.requirements.index', ['jurisdictionTypes' => [$location->type->id]]);
    //     $response = $this->get($jurisRoute)->assertSuccessful();
    //     $response->assertSee($work->title);
    // }

    // /**
    //  * @return void
    //  */
    // public function testLegalReport(): void
    // {
    //     [$user, $norma, $org] = $this->initUserNormaOrg();
    //     $routeName = 'my.corpus.requirements.legal-report';
    //     $route = route($routeName);

    //     $work = Work::factory()->create();
    //     $work2 = Work::factory()->create();
    //     $reference = Reference::factory()->for($work)->create();
    //     $referenc2 = Reference::factory()->for($work2)->create();
    //     $work3 = Work::factory()->create();
    //     $reference3 = Reference::factory()->for($work3)->create();
    //     $reference->normas()->attach($norma);
    //     $referenc2->normas()->attach($norma);

    //     $response = $this->get($route)->assertSuccessful();
    //     $response->assertSee($work->title);
    //     $response->assertSee($work2->title);
    //     $response->assertDontSee($work3->title);

    //     $activities = UserActivity::all();
    //     $this->assertTrue($activities->where('activity_type', UserActivityType::viewedRequirements()->value)->isNotEmpty());

    //     $location = Location::factory()->create();
    //     $reference->locations()->attach($location);
    //     $jurisRoute = route('my.corpus.requirements.legal-report', ['jurisdictionType' => $location->type->id]);
    //     $response = $this->get($jurisRoute)->assertSuccessful();
    //     $response->assertSee($work->title);
    // }

    // public function testLegalReportForOrg(): void
    // {
    //     [$user, $norma, $org] = $this->initUserNormaOrg();
    //     $norma2 = Norma::factory()->for($org)->create();
    //     $route = route('my.corpus.requirements.legal-report');

    //     app(ActiveNormasManager::class)->activateAll($user);

    //     $work = Work::factory()->create();
    //     $reference = Reference::factory()->for($work)->create();
    //     $work2 = Work::factory()->create();
    //     $reference2 = Reference::factory()->for($work2)->create();
    //     $reference->normas()->attach($norma);
    //     $reference2->normas()->attach($norma2);

    //     $response = $this->get($route)->assertSuccessful();
    //     $response->assertSee($work->title);
    //     $response->assertSee($work2->title);

    //     $location = Location::factory()->create();
    //     $reference->locations()->attach($location);

    //     $jurisRoute = route('my.corpus.requirements.legal-report', ['jurisdiction_types' => $location->type->id]);
    //     $response = $this->get($jurisRoute)->assertSuccessful();
    //     $response->assertSee($work->title);
    // }

    /**
     * @return void
     */
    public function testLegalRegister(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.corpus.requirements.legal-register';
        $route = route($routeName);

        $work = Work::factory()->create();
        $work2 = Work::factory()->create();
        $work->children()->attach($work2);
        $reference = Reference::factory()->for($work)->create();
        $referenc2 = Reference::factory()->for($work2)->create();
        $work3 = Work::factory()->create();
        $reference3 = Reference::factory()->for($work3)->create();
        $reference->normas()->attach($norma);
        $referenc2->normas()->attach($norma);
        $domain = LegalDomain::factory()->create();
        $reference->legalDomains()->attach($domain);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($work->title);
        $response->assertSee($work2->title);
        $response->assertDontSee($work3->title);

        $response = $this->get(route($routeName, ['domains' => [$domain->id]]))->assertSuccessful();
        $response->assertSee($work->title);
        $response->assertDontSee($work2->title);

        $activities = UserActivity::all();
        $this->assertTrue($activities->where('activity_type', UserActivityType::viewedRequirements()->value)->isNotEmpty());

        app(ActiveNormasManager::class)->activateAll($user);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee('Switch to single stream mode to see a legal register for a specific Norma Stream');
        $response->assertDontSee($work->title);
    }

    public function testIndex(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.corpus.requirements.index';
        $route = route($routeName);

        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($norma);

        $this->partialMock(DocumentSearch::class, function (MockInterface $mock) use ($work) {
            $work->load([
                'references.legalDomains', 'references.locations.type', 'references.locations.country',
                'references.citation', 'children.references.citation', 'references.refPlainText',
                'children.references.refPlainText', 'references.bookmarks', 'children.references.bookmarks',
            ]);
            $works = new LengthAwarePaginator((new Work())->newCollection([$work]), 1, 50);
            $references = new LengthAwarePaginator($work->references, $work->references->count(), 50);
            $mock->shouldReceive('getReferencesForRequirementsSearch')->andReturn($references);
            $mock->shouldReceive('addHighlightsToResults')->andReturn($works);
        });

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($work->title);
        $response->assertSee($work->children->first()->title);
        $reference = $work->references->load(['citation', 'refPlainText', 'bookmarks'])->first();
        $response->assertSee($reference->refPlainText?->plain_text);

        \App\Models\Ontology\CategoryType::factory()->create(['id' => CategoryType::SUBJECT->value]);
        \App\Models\Ontology\CategoryType::factory()->create(['id' => CategoryType::CONTROL->value]);
        $control = Category::factory()->create(['category_type_id' => CategoryType::CONTROL->value]);
        $reference->categories()->attach($control);
        $response = $this->get(route($routeName, ['controls' => [$control->id]]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
        $response->assertDontSee($work->references->last()->refPlainText?->plain_text);

        $category = Category::factory()->create(['category_type_id' => CategoryType::SUBJECT->value]);
        $reference->categories()->attach($category);
        $response = $this->get(route($routeName, ['topics' => [$category->id]]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
        $response->assertDontSee($work->references->last()->refPlainText?->plain_text);

        $tag2 = Tag::factory()->create();
        $reference->tags()->attach($tag2);
        $response = $this->get(route($routeName, ['tags' => [$tag2->id]]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
        $response->assertDontSee($work->references->last()->refPlainText?->plain_text);

        $requirementsCollection2 = RequirementsCollection::factory()->create();
        $reference->requirementsCollections()->attach($requirementsCollection2);
        $response = $this->get(route($routeName, ['jurisdictionTypes' => [$requirementsCollection2->id]]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
        $response->assertDontSee($work->references->last()->refPlainText?->plain_text);

        $domain2 = LegalDomain::factory()->create();
        $reference->legalDomains()->attach($domain2);
        $response = $this->get(route($routeName, ['domains' => [$domain2->id]]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
        $response->assertDontSee($work->references->last()->refPlainText?->plain_text);

        $response = $this->get(route($routeName, ['search' => $reference->refPlainText?->plain_text]))->assertSuccessful();

        [,, $work2, $requirementsCollection3, $domain3, $tag3] = $this->initCompiledStream($norma);

        $response = $this->get(route($routeName, ['works' => [$work2->id]]))->assertSuccessful();
        $response->assertSee($work2->title);
        $response->assertSee($work2->children->first()->title);
        $response->assertDontSee($work->title);

        $this->getJson(route('my.categories.tagging.search', ['search' => '']))
            ->assertSuccessful()
            ->assertDontSee($category->display_label);

        $this->getJson(route('my.categories.tagging.search', ['search' => substr($category->display_label, 0, 4)]))
            ->assertSuccessful()
            ->assertSee($category->display_label);

        $this->getJson(route('my.ontology.categories.search-suggest', ['key' => 'requirements-search', 'search' => substr($category->display_label, 0, 4)]))
            ->assertSuccessful()
            ->assertSee($category->display_label);

        $this->getJson(route('my.ontology.categories.controls.search-suggest', ['key' => 'requirements-search', 'search' => substr($control->display_label, 0, 4)]))
            ->assertSuccessful()
            ->assertSee($control->display_label);

        app(ActiveNormasManager::class)->activateAll($user);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee('Switch to single stream mode');

        $this->getJson(route('my.ontology.categories.search-suggest', ['key' => 'requirements-search', 'search' => substr($category->display_label, 0, 4)]))
            ->assertSuccessful()
            ->assertSee($category->display_label);
    }

    public function testShow(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        /** @var Work $work */
        $work = Work::factory()->hasExpressions(1)->create();
        $routeName = 'my.corpus.works.show';
        $response = $this->get(route($routeName, ['work' => $work]))->assertSuccessful();
        $response->assertSee($work->title);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName, ['work' => $work]))->assertSuccessful();

        $ex = $work->getCurrentExpression();
        $ex->update(['show_source_document' => false, 'converted_document_id' => $ex->source_document_id, 'source_document_id' => null]);

        $ref = Reference::factory()->create(['work_id' => $work->id]);

        $this->get(route('my.corpus.works.for-reference.show', ['reference' => $ref->id]))
            ->assertSuccessful()
            ->assertSee("/works/full-text/{$work->id}?fluid=1&scroll_to={$ref->id}");
    }

    public function testPreviewSource(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        /** @var Work */
        $work = Work::factory()->hasExpressions(1)->create();
        $work2 = Work::factory()->create();
        $expression = $work->expressions->first();
        $work->update(['active_work_expression_id' => $expression->id]);

        $document = $expression->sourceDocument;

        $routeName = 'my.corpus.works.preview.source';
        $response = $this->get(route($routeName, ['work' => $work2]))->assertSuccessful();
        $response->assertSee('There is no preview available');

        Storage::fake('local-documents');
        $storagePath = app(WorkStorageProcessor::class)->expressionPathSource($expression);
        $path = "{$storagePath}/test.html";
        $testContent = 'test content';
        Storage::disk('local-documents')->put($path, $testContent);
        Storage::disk('local-documents')->put($storagePath . '/style.css', $testContent);

        $document->update(['path' => $path]);

        $response = $this->get(route($routeName, ['work' => $work]))->assertSuccessful();
        $response = $this->get(route($routeName, ['work' => $work, 'file' => 'style.css']))->assertSuccessful();

        $document->update(['path' => 'not/exsists/']);
        $response = $this->withExceptionHandling()->get(route($routeName, ['work' => $work]))->assertNotFound();

        Storage::fake('local');
        $testContent2 = 'Should show this';
        Storage::disk('local')->put($path, $testContent2);
        // test for August content model source document
        $contentResource = app(ContentResourceStore::class)->storeResource($testContent2, 'text/plain');

        $doc = Doc::factory()->create(['first_content_resource_id' => $contentResource->id]);
        $work->update(['active_doc_id' => $doc->id]);

        $response = $this->get(route($routeName, ['work' => $work]))
            ->assertSuccessful()
            ->assertSee($testContent2);
    }

    public function testExportAsExcel(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.corpus.requirements.index.export.excel';
        Storage::fake();

        $work = Work::factory()->create();
        $work2 = Work::factory()->create();
        $reference = Reference::factory()->for($work)->create();
        $referenc2 = Reference::factory()->for($work2)->create();
        $reference->normas()->attach($norma);
        $referenc2->normas()->attach($norma);

        $response = $this->get(route($routeName))->assertSuccessful();

        $response->assertSee('redirect=');

        preg_match('/redirect=(.*)\"/', $response->getContent(), $matches);
        $redirectUrl = urldecode($matches[1]);
        $filename = urldecode(Str::afterLast($redirectUrl, '/'));

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::assertExists($path);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
    }

    public function testExportAsPDF(): void
    {
        PDF::fake();
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.corpus.requirements.index.export.pdf';
        Storage::fake();

        $work = Work::factory()->create();
        $work2 = Work::factory()->create();
        $reference = Reference::factory()->for($work)->create();
        $referenc2 = Reference::factory()->for($work2)->create();
        $reference->normas()->attach($norma);
        $referenc2->normas()->attach($norma);

        $response = $this->get(route($routeName))->assertSuccessful();

        $response->assertSee('redirect=');

        preg_match('/redirect=(.*)\"/', $response->getContent(), $matches);
        $redirectUrl = urldecode($matches[1]);
        $filename = urldecode(Str::afterLast($redirectUrl, '/'));

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $filename;
        Storage::assertExists($path);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
    }

    public function testPrintPreview(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $works = Work::factory(2)->hasReferences(2)->hasExpressions(1)->create();
        $works[0]->update(['active_work_expression_id' => $works[0]->expressions->first()->id]);

        $works[0]->references->each(function ($reference) {
            ReferenceContent::factory()->for($reference)->create();
            $reference->load('htmlContent');
        });

        $testText = 'Some test Text in the document';
        $mock = $this->mock(VolumeHighlighter::class, function (MockInterface $mock) use ($testText) {
            $mock->allows([
                'highlight' => '<html>' . $testText . '</html>',
            ]);
        });

        $route = route('my.corpus.works.print-preview', ['work' => $works[0]->id]);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($works[0]->references->first()->htmlContent?->text);
    }
}
