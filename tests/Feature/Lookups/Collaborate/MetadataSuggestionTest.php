<?php

namespace Tests\Feature\Lookups\Collaborate;

use App\Models\Actions\ActionArea;
use App\Models\Assess\AssessmentItem;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetadataSuggestionTest extends TestCase
{
    protected function evaluate(string $model, string $route): void
    {
        $this->collaboratorSignIn($this->collaborateSuperUser());

        $items = $model::factory()->count(5)
            ->create()
            ->map(function ($item) {
                $item->relevance = random_int(1, 1000);

                return ['document' => ['id' => $item->id], 'relevance' => $item->relevance];
            });

        $ref = Reference::factory()->create();

        $this->getJson(route($route, ['reference' => $ref->id]))
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');

        $content = ReferenceContent::factory()->create(['reference_id' => $ref->id]);

        Http::fake([
            'norma-ai.norma.rocks:7000/*' => Http::response([
                'query' => $content->cached_content,
                'results' => $items,
            ], 200, ['content-type' => 'application/json']),
        ]);

        $this->getJson(route($route, ['reference' => $ref->id]))
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');

        Config::set('services.norma_ai.enabled', true);
        Config::set('services.norma_ai.host', 'http://norma-ai.norma.rocks:7000');

        $sorted = $items->sortByDesc('relevance')->map(fn ($item) => ['id' => $item['document']['id']])->values()->all();

        $this->assertNotEquals($sorted, $items->all());

        $this->getJson(route($route, ['reference' => $ref->id]))
            ->assertSuccessful()
            ->assertJsonCount($model === LegalDomain::class ? 0 : 5, 'data')
            ->assertJson(['data' => $model === LegalDomain::class ? [] : $sorted]);
    }

    public function testAssessmentItemsGeneration(): void
    {
        $this->evaluate(AssessmentItem::class, 'api.collaborate.reference.suggest.assessment-items');
    }

    public function testCategoriesGeneration(): void
    {
        $this->evaluate(Category::class, 'api.collaborate.reference.suggest.categories');
    }

    public function testContextQuestionsGeneration(): void
    {
        $this->evaluate(ContextQuestion::class, 'api.collaborate.reference.suggest.context-questions');
    }

    public function testActionAreasGeneration(): void
    {
        $this->evaluate(ActionArea::class, 'api.collaborate.reference.suggest.action-areas');
    }

    public function testLegalDomainsGeneration(): void
    {
        $this->evaluate(LegalDomain::class, 'api.collaborate.reference.suggest.legal-domains');
    }
}
