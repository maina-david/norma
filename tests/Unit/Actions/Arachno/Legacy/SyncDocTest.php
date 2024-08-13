<?php

namespace Tests\Unit\Actions\Arachno\Legacy;

use App\Actions\Arachno\Legacy\SyncDoc;
use App\Models\Arachno\Link;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Services\Storage\WorkStorageProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncDocTest extends TestCase
{
    public function testSyncFromExpression(): void
    {
        Storage::fake();
        $testHtml = '<p>Test</p>';
        $this->mock(WorkStorageProcessor::class, function (MockInterface $mock) use ($testHtml) {
            $mock->shouldReceive('getCachedWorkVolume')->andReturn($testHtml);
        });
        $work = Work::factory()->has(Reference::factory(3))->create([
            'source_unique_id' => Str::random(),
        ]);
        $expression = WorkExpression::factory()->for($work)->create();
        SyncDoc::dispatch($expression);

        $doc = Doc::all()->load(['docMeta'])->first();
        $work = $expression->work;
        $this->assertSame($work->title, $doc->title);
        $this->assertSame($work->source_unique_id, $doc->source_unique_id);
        $this->assertSame($work->language_code, $doc->docMeta->language_code);
        $this->assertSame($work->id, $doc->work_id);

        $links = Link::all();
        $this->assertSame('https://my.libryo.com/corpus/legacy/' . $doc->uid_hash . '/volume/1', $links->first()->url);

        $contentResource = ContentResource::all()->first();
        $this->assertNotNull($contentResource);

        $tocItems = TocItem::all();
        $ref1 = $work->references->load(['citation', 'refPlainText'])->first();
        $ref2 = $work->references->load(['citation', 'refPlainText'])[1];
        $toc1 = $tocItems[0];
        $toc2 = $tocItems[1];
        $this->assertSame($ref1->refPlainText?->plain_text, $tocItems->first()->label);
        $this->assertSame($ref2->refPlainText?->plain_text, $toc2->label);
        $this->assertSame($ref1->level, $tocItems->first()->level);
        $this->assertSame($ref2->level, $toc2->level);
        $this->assertSame(0, $toc1->doc_position);
        $this->assertSame(1, $toc2->doc_position);
        $this->assertSame($doc->id, $toc1->doc_id);
        $this->assertSame($doc->id, $toc2->doc_id);
    }

    public function testReplaceIdsInContent(): void
    {
        $html = '<p data-type="section" data-level="2"><span data-inline="num" data-id="1557760"> <b> 2 </b> </span><span data-inline="heading" data-id="1557760"> <b> Commencement </b> </span></p><p></p><div></div>';
        $content = app(SyncDoc::class)->replaceIdsInContent($html);
        $this->assertStringContainsString(' id="id_1557760"', $content);
        $this->assertStringNotContainsString(' data-id="1557760"', $content);
    }
}
