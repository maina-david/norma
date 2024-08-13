<?php

namespace Tests\Feature\Corpus\Collaborate\ToC;

use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Enums\System\ProcessingJobStatus;
use App\Jobs\Corpus\GenerateReferencesFromVolume;
use App\Jobs\Middleware\Debounce;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\System\ProcessingJob;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class ToCReferenceControllerTest extends TestCase
{
    public function testLoadingReferences(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();
        $this->setExpression($expression);

        $reference = Reference::factory()->create(['volume' => 1, 'work_id' => $expression->work_id, 'type' => ReferenceType::citation()->value]);
        $referenceNotInWork = Reference::factory()->create(['volume' => 1, 'type' => ReferenceType::citation()->value]);

        $this->json('GET', route('collaborate.toc.references.volume.index', ['expression' => $expression->id, 'volume' => 1]))
            ->assertSuccessful()
            ->assertSee($reference->text)
            ->assertDontSee($referenceNotInWork->text);
    }

    public function testGeneration(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $content = '<h1>Awesome Text!</h1><span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>';
        $expression = WorkExpression::factory()->create(['volume' => 5]);
        $this->putContent(route('collaborate.toc.content.update', ['expression' => $expression->id, 'volume' => 1]), $content)
            ->assertSuccessful();

        $this->setExpression($expression);

        Debounce::fake();

        ProcessingJob::created(fn ($item) => $item->update([
            'payload' => $this->customPayload(),
            'status' => ProcessingJobStatus::complete()->value,
        ]));

        GenerateReferencesFromVolume::fakeHighlighter([
            new Response(200, ['Content-Type' => 'text/html'], '<h1>Awesome Text!</h1>'),
            new Response(200, ['Content-Type' => 'text/html'], '<h1>Awesome Text!</h1>'),
            new Response(200, ['Content-Type' => 'text/html'], '<h1>Awesome Text!</h1>'),
        ]);

        $this->assertDatabaseMissing(Reference::class, ['work_id' => $expression->work_id, 'type' => ReferenceType::citation()->value]);

        $this->json('POST', route('collaborate.toc.references.validate', ['expression' => $expression->id]))
            ->assertSuccessful()
            ->assertJson(['invalid' => [2, 3, 4]])
            ->assertJsonCount(3, 'invalid');

        $this->json('POST', route('collaborate.toc.references.generate', ['expression' => $expression->id]))
            ->assertSuccessful();

        $this->assertDatabaseHas(Reference::class, ['work_id' => $expression->work_id, 'type' => ReferenceType::citation()->value]);

        $reference = Reference::where(['work_id' => $expression->work_id, 'type' => ReferenceType::citation()->value])->firstOrFail();

        $this->json('POST', route('collaborate.toc.references.generate', ['expression' => $expression->id]))
            ->assertSuccessful();

        $payload = [
            'selectors' => [1, 2],
            'title' => 'Something Cool',
            'text' => 'Something Cool',
            'start' => 10000,
            'level' => 4,
            'is_section' => 1,
            'volume' => 1,
        ];

        $this->json('PUT', route('collaborate.toc.references.update', ['expression' => $expression->id, $reference->id]), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'title' => 'Something Cool',
                    'start' => 10000,
                    'level' => 4,
                    'is_section' => 1,
                    'volume' => 1,
                ],
            ]);

        Debounce::fake(false);
    }

    public function testApplicationAndDeletion(): void
    {
        $this->signIn($this->collaborateSuperUser());

        $expression = WorkExpression::factory()->create();

        $reference = Reference::factory()->create(['work_id' => $expression->work_id, 'status' => ReferenceStatus::pending()->value]);

        $this->json('PUT', route('collaborate.toc.work.references.apply', ['expression' => $expression->id]))
            ->assertSuccessful();

        $this->assertTrue(ReferenceStatus::active()->is($reference->refresh()->status));

        $this->assertDatabaseHas(Reference::class, ['id' => $reference->id, 'deleted_at' => null]);

        $this->json('DELETE', route('collaborate.toc.references.destroy', ['expression' => $expression->id, 'reference' => $reference->id]))
            ->assertSuccessful();

        $this->assertDatabaseMissing(Reference::class, ['id' => $reference->id, 'deleted_at' => null]);
    }

    protected function customPayload(): array
    {
        return [
            197221 => [
                'id' => '197221',
                'num' => '  5  ',
                'type' => 'section',
                'level' => '2',
                'volume' => 1,
                'content' => '<span data-inline="num" data-id="197221"> <b> 5 </b> </span><span data-inline="heading" data-id="197221"> <b> Act not to apply to transport of dangerous goods covered by other scheme </b> </span>',
                'heading' => '  Act not to apply to transport of dangerous goods covered by other scheme  ',
                'selectors' => [
                    [
                        'type' => 'RangeSelector',
                        'endOffset' => 76,
                        'startOffset' => 0,
                        'endContainer' => '/p[22]/span[2]',
                        'startContainer' => '/p[22]/span[1]',
                    ],
                    [
                        'end' => 3165,
                        'type' => 'TextPositionSelector',
                        'start' => 3084,
                    ],
                    [
                        'type' => 'TextQuoteSelector',
                        'exact' => '  5    Act not to apply to transport of dangerous goods covered by other scheme  ',
                        'prefix' => ' specified in the regulations). ',
                        'suffix' => ' To the extent to which it is re',
                    ],
                ],
                'is_section' => true,
            ],
        ];
    }
}
