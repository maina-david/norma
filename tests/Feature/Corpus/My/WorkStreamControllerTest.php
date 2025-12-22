<?php

namespace Tests\Feature\Corpus\My;

use App\Models\Corpus\Doc;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Services\Corpus\VolumeHighlighter;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\Feature\My\MyTestCase;

class WorkStreamControllerTest extends MyTestCase
{
    public function testShowWithRelations(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $works = Work::factory(2)->hasReferences(2)->create();
        $works[0]->references->first()->normas()->attach($norma);
        $works[1]->references->first()->normas()->attach($norma);
        $works[1]->parents()->attach($works[0]);
        $route = route('my.requirements.with.relation.show', ['work' => $works[0]->id]);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($works[1]->title);
        $response->assertSee('Subsidiary Documents');

        app(ActiveNormasManager::class)->activateAll($user);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($works[1]->title);
    }

    public function testFullText(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $works = Work::factory(2)->hasReferences(2)->hasExpressions(1)->create();
        $works[0]->update(['active_work_expression_id' => $works[0]->expressions->first()->id]);

        $works[0]->references->each(function ($reference) {
            ReferenceContent::factory()->for($reference)->create();
        });

        $works->load(['references.citation', 'references.refPlainText', 'references.htmlContent']);

        $mock = $this->mock(VolumeHighlighter::class, function (MockInterface $mock) {
            $mock->allows([
                'highlight' => '<html>Some Text</html>',
            ]);
        });

        $route = route('my.works.full-text.show', ['work' => $works[0]->id]);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($works[0]->references->first()->refPlainText?->plain_text);

        $route = route('my.corpus.works.full-text.from-references', ['work' => $works[0]->id]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($works[0]->references->first()->htmlContent?->text);

        // test  august model full text
        /** @var Doc */
        $doc = Doc::factory()->create();
        $expression = $works[0]->expressions->first();
        $expression->update(['doc_id' => $doc->id]);
        $route = route('my.works.full-text.show', ['work' => $works[0]->id]);
        $response = $this->get($route)->assertSuccessful();
    }

    public function testSearchSuggest(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream($norma);

        $routeName = 'my.corpus.works.search-suggest';
        // test without a search query
        $this->get(route($routeName, ['key' => 'reference-suggest']))
            ->assertSuccessful()
            ->assertDontSee($work->title);

        $this->activateAllStreams($user);

        // can't test fulltext search with database transactions, so just test success
        $this->get(route($routeName, ['key' => 'reference-suggest', 'search' => substr($work->title, 0, 3)]))
            ->assertSuccessful();

        $this->deleteCompiledStream();
    }

    public function testSearchSuggestAll(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $route = route('my.corpus.works.search-suggest-all', ['key' => 'requirements-page']);

        $searchTerm = Str::random();
        $response = $this->get($route)->assertSuccessful();
        $response = $this->post($route, ['search' => $searchTerm])
            ->assertSuccessful()
            ->assertSee($searchTerm);

        app(ActiveNormasManager::class)->activateAll($user);

        $route = route('my.corpus.works.search-suggest-all', ['key' => 'requirements-search', 'search' => $searchTerm]);
        $response = $this->get($route)->assertSuccessful()
            ->assertSee($searchTerm);
    }
}
