<?php

namespace Tests\Unit\Actions\Corpus\Doc;

use App\Actions\Corpus\Doc\DeleteFull;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Notify\LegalUpdate;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteFullTest extends TestCase
{
    public function testHandle(): void
    {
        Storage::fake();

        $content = 'Hello';
        $resource = app(ContentResourceStore::class)->storeResource($content, 'text/plain');
        $doc = Doc::factory()->create([
            'first_content_resource_id' => $resource->id,
        ]);
        $tocItems = TocItem::factory(3)->for($doc)->create(['content_resource_id' => $resource->id]);
        $id = $doc->id;
        $this->assertNotNull(Doc::find($id));
        DeleteFull::run($doc);
        $this->assertNull(Doc::find($id));
        $this->assertTrue(TocItem::where('doc_id', $id)->count() === 0);
        $this->assertNull(ContentResource::find($resource->id));
        $this->assertNull(app(ContentResourceStore::class)->get($resource->path));

        // if the resource is used elsewhere, (e.g. legal update) then don't delete the resource
        $resource = app(ContentResourceStore::class)->storeResource($content, 'text/plain');
        $doc = Doc::factory()->create([
            'first_content_resource_id' => $resource->id,
        ]);
        LegalUpdate::factory()->create(['content_resource_id' => $resource->id]);
        $this->runFailedDelete($doc, $resource, $content);

        $doc = Doc::factory()->create();
        $doc->docMeta->update(['summary_content_resource_id' => $resource->id]);
        $this->runFailedDelete($doc, $resource, $content);
    }

    private function runFailedDelete(Doc $doc, ContentResource $resource, string $content): void
    {
        DeleteFull::run($doc);
        $this->assertNotNull(ContentResource::find($resource->id));
        $this->assertSame($content, app(ContentResourceStore::class)->get($resource->path));
    }
}
