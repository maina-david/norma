<?php

namespace Tests\Feature\Corpus;

use App\Jobs\Corpus\GenerateReferenceContentExtracts;
use App\Models\Actions\ActionArea;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\ReferenceContentExtract;
use App\Models\Requirements\ReferenceRequirement;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\UsesNormaAI;

class GenerateReferenceContentExtractsTest extends TestCase
{
    use UsesNormaAI;

    public function testGenerationOfContent(): void
    {
        Config::set('services.norma_ai.enabled', true);
        Config::set('services.norma_ai.host', 'http://norma-ai.test');
        /** @var Reference $reference */
        $reference = Reference::factory()->create();
        /** @var ActionArea $actionArea */
        $actionArea = ActionArea::factory()->create();
        $reference->actionAreas()->attach($actionArea);

        ReferenceContent::upsert([
            'cached_content' => '<p data-type="section" data-level="2" data-is_section="1"><span data-inline="num" data-id="197231"> <b> 18 </b> </span><span data-inline="heading" data-id="197231"> <b> Offences relating to licences and security clearances </b> </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (1) </span><span> A person must not pretend to hold a licence or security clearance. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (2) </span><span> A person must not, for the purpose of obtaining a licence or security clearance, provide any information or produce any document that the person knows is false or misleading in a material particular. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (3) </span><span> A person must not, with intent to deceive, forge or alter a licence or security clearance. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (4) </span><span> A person must not, without reasonable excuse, have another person’s licence or security clearance in his or her possession. </span></p><p data-type="subsection" data-indent="2"><span data-inline="num"> (5) </span><span> A holder of a licence or security clearance must not lend the licence or security clearance or allow it to be used by any other person. </span></p><div data-indent="2"> Maximum penalty: 50 penalty units. </div>',
            'reference_id' => $reference->id,
        ], 'reference_id');

        $reference->refRequirement()->delete();
        $reference->requirementDraft()->delete();

        $expected = $this->getExpectations();
        $this->mockNormaAIGenerateTaskRequest();

        $this->assertSame(0, $reference->contentExtracts()->count());

        collect($expected)->each(fn ($item) => $this->assertDatabaseMissing(ReferenceContentExtract::class, ['content' => $item]));

        dispatch(new GenerateReferenceContentExtracts($reference->work_id));

        // No requirements so nothing is generated
        $this->assertSame(0, $reference->contentExtracts()->count());

        collect($expected)->each(fn ($item) => $this->assertDatabaseMissing(ReferenceContentExtract::class, ['content' => $item]));

        ReferenceRequirement::factory()->for($reference)->create();

        dispatch(new GenerateReferenceContentExtracts($reference->work_id));

        $this->assertSame(6, $reference->contentExtracts()->count());

        collect($expected)->each(fn ($item) => $this->assertDatabaseHas(ReferenceContentExtract::class, ['content' => $item]));

        $this->signIn($this->collaborateSuperUser());

        $this->getJson(route('api.collaborate.references.content.extracts.show', ['reference' => $reference->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => collect($expected)->map(fn ($item) => ['content' => $item])->all(),
            ]);
    }
}
