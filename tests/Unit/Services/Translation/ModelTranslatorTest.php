<?php

namespace Tests\Unit\Services\Translation;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Lookups\Translation;
use App\Models\Requirements\Summary;
use App\Services\Translation\ModelTranslator;
use DomainException;
use Tests\TestCase;
use Tests\Traits\MocksTranslatorService;

class ModelTranslatorTest extends TestCase
{
    use MocksTranslatorService;

    public function testTranslateSummary(): void
    {
        $this->mockTranslator();
        $count = Translation::count();
        $reference = Reference::factory()->create();
        $summary = Summary::factory()->create(['reference_id' => $reference->id]);
        $text = app(ModelTranslator::class)->translateSummary($summary, 'de');

        $this->assertNotNull($text);
    }

    public function testTranslateReference(): void
    {
        $this->mockTranslator();
        $ref = Reference::factory()->create();
        $ref->htmlContent()->save(new ReferenceContent([
            'reference_id' => $ref->id,
            'cached_content' => $this->faker->randomHtml(1, 2),
        ]));
        Translation::factory()->create([
            'register_item_id' => $ref->id,
            'source_language' => 'en',
            'target_language' => 'de',
        ]);
        $count = Translation::count();
        $text = app(ModelTranslator::class)->translateReference($ref, 'de');
        $this->assertSame($count, Translation::count());
        $text = app(ModelTranslator::class)->translateReference($ref, 'fr');
        $this->assertSame($count, Translation::count());

        $this->expectException(DomainException::class);
        app(ModelTranslator::class)->translateReference($ref, 'en');
    }
}
