<?php

namespace Tests\Unit\Actions\Arachno;

use App\Actions\Arachno\AddCatalogueDocsToFrontier;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\UrlFrontierLink;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Work;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddCatalogueDocsToFrontierTest extends TestCase
{
    public function testHandle(): void
    {
        Storage::fake();
        Queue::fake();

        /** @var Crawler */
        $crawler = Crawler::factory()->create();
        $url = 'http://example.com/test';
        /** @var CatalogueDoc */
        $catalogueDoc = CatalogueDoc::factory()->create([
            'start_url' => $url,
            'crawler_id' => $crawler->id,
        ]);
        app(AddCatalogueDocsToFrontier::class)->handle([$catalogueDoc->id]);

        /** @var UrlFrontierLink */
        $urlLink = UrlFrontierLink::where('url', $url)->first();
        $this->assertNotNull($urlLink);
        $this->assertNotNull($catalogueDoc->refresh()->fetch_started_at);

        $uid = hex2bin(sha1(uniqid()));
        $catalogueDoc->update(['uid' => $uid, 'fetch_started_at' => null]);
        UrlFrontierLink::all()->each(fn ($e) => $e->delete());
        app(AddCatalogueDocsToFrontier::class)->handle([$catalogueDoc->id], true);
        /** @var UrlFrontierLink */
        $urlLink = UrlFrontierLink::where('url', $url)->first();
        $this->assertNull($urlLink);
        $this->assertNull($catalogueDoc->refresh()->fetch_started_at);

        $catalogueDoc->update(['uid' => $uid, 'fetch_started_at' => null]);
        UrlFrontierLink::all()->each(fn ($e) => $e->delete());
        $work = Work::factory()->create(['uid' => $uid]);
        app(AddCatalogueDocsToFrontier::class)->handle([$catalogueDoc->id], true);
        /** @var UrlFrontierLink */
        $urlLink = UrlFrontierLink::where('url', $url)->first();
        $this->assertNotNull($urlLink);
        $this->assertNotNull($catalogueDoc->refresh()->fetch_started_at);
    }
}
