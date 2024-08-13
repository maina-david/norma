<?php

namespace Tests\Unit\Actions\Notify\LegalUpdate;

use App\Actions\Notify\LegalUpdate\CreateFromDoc;
use App\Models\Arachno\Source;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\WorkType;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateFromDocTest extends TestCase
{
    public function testHandle(): void
    {
        $fullSummary = Str::random(500);
        $this->partialMock(ContentResourceStore::class, function (MockInterface $mock) use ($fullSummary) {
            $mock->shouldReceive('get')->andReturn($fullSummary);
        });
        $source = Source::factory()->create();
        $contentResource = ContentResource::factory()->create(['path' => '/filepath']);
        $doc = Doc::factory()->create([
            'source_id' => $source->id,
            'primary_location_id' => Location::factory()->create()->id,
        ]);
        $category = Category::factory()->create();
        $question = ContextQuestion::factory()->create();
        $category->contextQuestions()->attach($question);
        $doc->categories()->attach($category);
        $doc->docMeta->update([
            'title_translation' => Str::random(),
            'publication_number' => Str::random(),
            'work_number' => Str::random(),
            'publication_document_number' => Str::random(),
            'work_type_id' => WorkType::factory()->create(['title' => 'Act', 'slug' => 'act'])->id,
            'work_date' => '2022-01-01',
            'language_code' => 'eng',
            'source_url' => Str::random(),
            'summary' => Str::random(),
            'summary_content_resource_id' => $contentResource->id,
            'effective_date' => '2022-01-02',
        ]);
        $legalUpdate = CreateFromDoc::run($doc);

        $this->assertSame($doc->title, $legalUpdate->title);
        $this->assertSame($doc->source_unique_id, $legalUpdate->source_unique_id);
        $this->assertSame($doc->source_id, $legalUpdate->source_id);
        $this->assertSame($doc->docMeta->title_translation, $legalUpdate->title_translation);
        $this->assertSame($doc->docMeta->publication_document_number, $legalUpdate->publication_document_number);
        $this->assertSame($doc->docMeta->publication_number, $legalUpdate->publication_number);
        $this->assertSame($doc->docMeta->work_number, $legalUpdate->work_number);
        $this->assertSame($doc->docMeta->work_date, $legalUpdate->publication_date->format('Y-m-d'));
        $this->assertSame($doc->docMeta->language_code, $legalUpdate->language_code);
        $this->assertSame($doc->primary_location_id, $legalUpdate->primary_location_id);
        $this->assertSame('<p>' . $fullSummary . '</p>', $legalUpdate->highlights);
        $this->assertSame($doc->docMeta->effective_date, $legalUpdate->effective_date->format('Y-m-d'));
        $this->assertTrue($legalUpdate->contextQuestions->contains($question));
    }
}
