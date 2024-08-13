<?php

namespace Tests\Unit\Actions\Corpus\Work;

use App\Actions\Corpus\Doc\LinkToWork;
use App\Actions\Corpus\Work\CreateFromDoc;
use App\Models\Arachno\Source;
use App\Models\Corpus\Doc;
use App\Models\Geonames\Location;
use App\Models\Ontology\WorkType;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateFromDocTest extends TestCase
{
    public function testHandle(): void
    {
        $this->partialMock(LinkToWork::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle');
        });
        $source = Source::factory()->create();
        $doc = Doc::factory()->create([
            'source_unique_id' => Str::random(),
            'source_id' => $source->id,
            'primary_location_id' => Location::factory()->create()->id,
        ]);
        $doc->docMeta->update([
            'title_translation' => Str::random(),
            'publication_number' => Str::random(),
            'work_number' => Str::random(),
            'publication_document_number' => Str::random(),
            'work_type_id' => WorkType::factory()->create(['title' => 'Act', 'slug' => 'act'])->id,
            'work_date' => '2022-01-01',
            'language_code' => 'eng',
            'source_url' => Str::random(),
            // 'summary' => Str::random(),
            'effective_date' => '2022-01-02',
        ]);
        $doc->setUid()->save();
        $work = CreateFromDoc::run($doc);

        $this->assertSame($doc->uid, $work->uid);
        $this->assertSame($doc->title, $work->title);
        $this->assertSame($doc->source_unique_id, $work->source_unique_id);
        $this->assertSame($doc->source_id, $work->source_id);
        $this->assertSame($doc->docMeta->title_translation, $work->title_translation);
        $this->assertSame($doc->docMeta->publication_number, $work->gazette_number);
        $this->assertSame($doc->docMeta->work_number, $work->work_number);
        $this->assertSame($doc->docMeta->work_date, $work->work_date->format('Y-m-d'));
        $this->assertSame($doc->docMeta->language_code, $work->language_code);
        $this->assertSame($doc->docMeta->source_url, $work->source_url);
        $this->assertSame($doc->primary_location_id, $work->primary_location_id);
        // $this->assertSame($doc->summary, $work->highlights);
        $this->assertSame($doc->docMeta->effective_date, $work->effective_date->format('Y-m-d'));
        $this->assertSame('act', $work->work_type);
    }
}
