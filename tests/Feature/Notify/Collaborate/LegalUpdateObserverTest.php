<?php

namespace Tests\Feature\Notify\Collaborate;

use App\Models\Corpus\Doc;
use App\Models\Notify\LegalUpdate;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\UsesLibryoAI;

class LegalUpdateObserverTest extends TestCase
{
    use UsesLibryoAI;

    /**
     * @return void
     */
    public function testHighlightsAndSummaryFieldsArePopulated(): void
    {
        $content = '<h1>This is the test content</h1>';
        $store = $this->app->make(ContentResourceStore::class);

        Storage::fake();

        $resource = $store->storeResource($content, 'text/html');
        $doc = Doc::factory()->create(['first_content_resource_id' => $resource]);
        $this->assertNotNull($resource->text_content_resource_id);

        $summary = 'The legal notification includes the creation of the Riverston Bay Marine Zone with restrictions on engine-powered ships, exceptions for Gladstone Area Water Board activities, and an updated definition of "government entity".';
        $highlights = "- Establishment of the Riverston Bay Marine Zone where engine-powered ships are banned, except for certain activities by Gladstone Area Water Board employees.\n- Change in the definition of \"government entity\" in Schedule 9 of the Transport Operations (Marine Safety) Regulation 2016.\n- Prohibition of engine-powered ships in the Riverston Bay Marine Zone, with exceptions for specific Gladstone Area Water Board activities.\n- Updated definition of \"government entity\" in the regulation.";

        Config::set('services.libryo_ai.enabled', true);
        Config::set('services.libryo_ai.host', 'http://libryo-ai.test');
        Http::fake([
            'libryo-ai.test/*' => Http::sequence()
                ->push([
                    'summary' => $summary,
                    'highlights' => $highlights,
                ], 200, ['content-type' => 'application/json']),
        ]);

        $update = LegalUpdate::factory()->create(['created_from_doc_id' => $doc->id]);

        $update->refresh();

        $this->assertSame($summary, $update->automated_update_report);
        $this->assertSame($highlights, $update->automated_highlights);

        Storage::disk($store->getDiskName())->delete($resource->path);
        Storage::disk($store->getDiskName())->delete($resource->textContentResource->path);
    }
}
