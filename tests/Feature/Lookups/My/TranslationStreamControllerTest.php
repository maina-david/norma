<?php

namespace Tests\Feature\Lookups\My;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Notify\LegalUpdate;
use App\Models\Requirements\Summary;
use App\Services\Customer\ActiveLibryosManager;
use Tests\Feature\My\MyTestCase;
use Tests\Traits\MocksTranslatorService;

class TranslationStreamControllerTest extends MyTestCase
{
    use MocksTranslatorService;

    public function testTranslateLegalUpdate(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.lookups.translations.translate.legal-update';

        $update = LegalUpdate::factory()->create();
        $this->mockTranslator($update->highlights);

        $this->withExceptionHandling()
            ->post(route($routeName, ['update' => $update->id]), ['language' => 'fr'])
            ->assertForbidden();

        $org->update(['translation_enabled' => true]);
        app(ActiveLibryosManager::class)->flushCache();

        $response = $this->post(route($routeName, ['update' => $update->id]), ['language' => 'fr'])
            ->assertSuccessful();

        $response->assertSeeText(strip_tags($update->highlights));

        // without language should reset
        $response = $this->withExceptionHandling()
            ->post(route($routeName, ['update' => $update->id]))
            ->assertSuccessful();
        $response->assertSeeText(strip_tags($update->highlights));
    }

    public function testTranslateSummary(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.lookups.translations.translate.summary';

        $reference = Reference::factory()->create();
        $summary = Summary::factory()->create(['reference_id' => $reference->id]);
        $this->mockTranslator($summary->summary_body);

        $this->withExceptionHandling()
            ->post(route($routeName, ['summary' => $summary->reference_id]), ['language' => 'fr'])
            ->assertForbidden();

        $org->update(['translation_enabled' => true]);
        app(ActiveLibryosManager::class)->flushCache();

        $response = $this->post(route($routeName, ['summary' => $summary->reference_id]), ['language' => 'fr'])
            ->assertSuccessful();
        $response->assertSeeText(strip_tags($summary->summary_body));

        // without language should reset
        $response = $this->withExceptionHandling()
            ->post(route($routeName, ['summary' => $summary->reference_id]))
            ->assertSuccessful();
        $response->assertSeeText(strip_tags($summary->summary_body));
    }

    public function testTranslateReference(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.lookups.translations.translate.reference';

        $html = '<p>Deutscher text</p>';
        $reference = Reference::factory()->create();
        $this->mockTranslator('');
        $reference->htmlContent()->save(new ReferenceContent([
            'reference_id' => $reference->id,
            'cached_content' => $html,
        ]));
        $this->mockTranslator($html);
        $reference->work->update(['language_code' => 'deu']);

        $org->update(['translation_enabled' => true]);
        app(ActiveLibryosManager::class)->flushCache();

        $response = $this->withExceptionHandling()
            ->get(route($routeName, ['reference' => $reference->id]))
            ->assertSuccessful();
        $response->assertSeeText(strip_tags($html));
    }
}
