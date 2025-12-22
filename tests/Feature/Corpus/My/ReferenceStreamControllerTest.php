<?php

namespace Tests\Feature\Corpus\My;

use App\Enums\System\NormaModule;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class ReferenceStreamControllerTest extends MyTestCase
{
    private function start(): array
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $works = Work::factory(2)->hasReferences(2)->create();
        $works[0]->references->first()->normas()->attach($norma);
        $works->load(['references.citation', 'references.refPlainText']);

        return [$user, $norma, $org, $works];
    }

    public function testShowYourRequirements(): void
    {
        [$user, $norma, $org, $works] = $this->start();

        $route = route('my.references.for.requirements', ['work' => $works[0]->id]);

        $works[0]->references->load('refPlainText');
        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($works[0]->references->first()->refPlainText?->plain_text);

        app(ActiveNormasManager::class)->activateAll($user);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($works[0]->references->first()->refPlainText?->plain_text);
    }

    public function testShow(): void
    {
        [$user, $norma, $org, $works] = $this->start();
        $route = route('my.references.partial.show', ['reference' => $works[0]->references->first()]);

        /** @var \App\Models\Customer\Norma $norma */
        $norma->enableModule(NormaModule::actions());

        $text = 'Some html content';
        $html = '<p>' . $text . '</p>';
        $ref = $works[0]->references->first();
        $ref->htmlContent()->save(new ReferenceContent(['reference_id' => $ref->id, 'cached_content' => $html]));

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($text);

        app(ActiveNormasManager::class)->activateAll($user);

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($text);
    }

    public function testIndexForAssessmentItemREsponse(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $works = Work::factory(2)->hasReferences(2)->create();

        $reference = $works[0]->references->first();
        $reference2 = $works[0]->references[1];
        $reference->load(['citation', 'refPlainText']);
        $reference2->load(['citation', 'refPlainText']);

        $reference->normas()->attach($norma);
        $reference2->normas()->attach($norma);
        $assessmentItem = AssessmentItem::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->for($assessmentItem)->for($norma)->create();
        $reference->assessmentItems()->attach($assessmentItem);

        $routeName = 'my.references.for.assessment-item-response.index';
        $response = $this->get(route($routeName, ['aiResponse' => $aiResponse]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
        $response->assertDontSee($reference2->refPlainText?->plain_text);

        app(ActiveNormasManager::class)->activateAll($user);

        $response = $this->get(route($routeName, ['aiResponse' => $aiResponse]))->assertSuccessful();
        $response->assertSee($reference->refPlainText?->plain_text);
        $response->assertDontSee($reference2->refPlainText?->plain_text);
    }

    public function testSuggest(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();

        $route = route('my.corpus.references.search-suggest', ['key' => 'requirements-page']);

        $searchTerm = Str::random();
        $response = $this->get($route)->assertSuccessful();
        $response = $this->post($route, ['search' => $searchTerm])
            ->assertSuccessful()
            ->assertSee($searchTerm);

        app(ActiveNormasManager::class)->activateAll($user);

        $route = route('my.corpus.references.search-suggest', ['key' => 'detailed-requirements-page', 'search' => $searchTerm]);
        $response = $this->get($route)->assertSuccessful()
            ->assertSee($searchTerm);
    }

    public function testShowFullText(): void
    {
        [$user, $norma, $org, $works] = $this->start();
        $route = route('my.references.partial.show.full-text', ['reference' => $works[0]->references->first()]);

        $text = 'Some html content';
        $html = '<p>' . $text . '</p>';
        $ref = $works[0]->references->first();
        $ref->htmlContent()->save(new ReferenceContent(['reference_id' => $ref->id, 'cached_content' => $html]));

        $response = $this->get($route)->assertSuccessful();
        $response->assertSee($text);
    }
}
