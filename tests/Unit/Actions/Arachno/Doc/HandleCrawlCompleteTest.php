<?php

namespace Tests\Unit\Actions\Arachno\Doc;

use App\Actions\Arachno\Doc\HandleCrawlComplete;
use App\Actions\Corpus\Work\CreateFromDoc;
use App\Models\Arachno\Link;
use App\Models\Arachno\Source;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Services\Corpus\ReferenceGenerator;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class HandleCrawlCompleteTest extends TestCase
{
    public function testHandle(): void
    {
        Storage::fake();
        Queue::fake();
        $this->partialMock(ReferenceGenerator::class, function (MockInterface $mock) {
            $mock->shouldReceive('forWork');
        });
        $link = Link::factory()->create();
        $source = Source::factory()->create();
        $doc = Doc::factory()->create(['source_unique_id' => Str::random(), 'source_id' => $source->id]);
        $doc->setUid()->save();
        $doc2 = Doc::factory()->create(['start_link_id' => $link->id]);
        $resource = ContentResource::factory()->create();
        $link->contentResources()->attach($resource);
        $tocItem = TocItem::factory()->for($doc)->create([
            'uri_fragment' => 'one',
            'position' => 0,
            'doc_position' => 0,
            'link_id' => $link->id,
            'content_resource_id' => $resource->id,
        ]);
        $tocItem2 = TocItem::factory()->for($doc)->create([
            'uri_fragment' => 'two',
            'position' => 1,
            'doc_position' => 1,
            'link_id' => $link->id,
            'content_resource_id' => null,
        ]);

        CatalogueDoc::factory()->create(['uid' => $doc->uid, 'fetch_started_at' => now()]);
        HandleCrawlComplete::run($doc->id);
        $this->assertTrue($doc->refresh()->first_content_resource_id === $resource->id);
        $this->assertNotNull($doc->version_hash);
        $this->assertNotNull($tocItem2->refresh()->content_resource_id);

        HandleCrawlComplete::run($doc2->id);
        $this->assertTrue($doc2->refresh()->first_content_resource_id === $resource->id);

        $work = Work::factory()->create(['uid' => $doc->uid]);
        $doc->update(['work_id' => $work->id, 'version_hash' => Str::random(40)]);
        HandleCrawlComplete::run($doc->id);
        $doc->update(['work_id' => null, 'version_hash' => Str::random(40)]);
        HandleCrawlComplete::run($doc->id);
        $this->assertSame($work->id, $doc->refresh()->work_id);

        $doc3 = Doc::factory()->create(['source_unique_id' => $doc->source_unique_id, 'source_id' => $source->id, 'version_hash' => Str::random(40)]);
        $doc3->setUid()->save();
        HandleCrawlComplete::run($doc3->id);
        $this->assertSame($work->id, $doc3->refresh()->work_id);
        CreateFromDoc::assertPushed();
    }
}
