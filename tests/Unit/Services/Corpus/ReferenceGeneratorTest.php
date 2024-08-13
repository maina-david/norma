<?php

namespace Tests\Unit\Services\Corpus;

use App\Models\Arachno\Source;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Services\Corpus\ReferenceGenerator;
use App\Services\Corpus\TocItemContentExtractor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class ReferenceGeneratorTest extends TestCase
{
    public function testGenerateForWork(): void
    {
        Storage::fake();

        $source = Source::factory()->create();
        $doc = Doc::factory()->create(['source_unique_id' => Str::random(), 'source_id' => $source->id]);
        $work = Work::factory()->create();
        $tocItems = TocItem::factory(5)
            ->for($doc)
            ->sequence(fn ($sequence) => ['doc_position' => $sequence->index + 1])
            ->sequence(fn ($sequence) => ['source_unique_id' => $sequence->index + 1])
            ->create([
                'level' => 1,
            ]);
        foreach ($tocItems as $ind => $item) {
            $item->_content = '<div>Section ' . ($ind + 1) . '</div>';
            $item->setUid()->save();
        }

        $this->mock(TocItemContentExtractor::class, function (MockInterface $mock) use ($tocItems) {
            $mock->shouldReceive('extract')->andReturn($tocItems);
        });

        app(ReferenceGenerator::class)->forWork($work, $doc);

        $references = $work->references()->with(['refPlainText', 'htmlContent'])->get();
        $ref1 = $references[0];
        $ref2 = $references[1];

        $this->assertSame($tocItems[0]->level, $ref1->level);
        $this->assertSame($tocItems[0]->doc_position, $ref1->position);
        $this->assertSame($tocItems[0]->label, $ref1->refPlainText?->plain_text);

        $this->assertSame($tocItems[1]->doc_position, $ref2->position);
        $this->assertSame($tocItems[1]->label, $ref2->refPlainText?->plain_text);

        $work->refresh();
        $tocItems[0]->_content = 'new content';
        $tocItems[0]->update([
            'content_hash' => sha1($tocItems[0]->_content),
            'label' => 'New Label',
        ]);

        $this->mock(TocItemContentExtractor::class, function (MockInterface $mock) use ($tocItems) {
            $mock->shouldReceive('extract')->andReturn($tocItems);
        });
        app(ReferenceGenerator::class)->forWork($work, $doc);
        $this->assertSame($tocItems[0]->_content, $ref1->refresh()->load('htmlContent')->htmlContent->cached_content);
    }
}
