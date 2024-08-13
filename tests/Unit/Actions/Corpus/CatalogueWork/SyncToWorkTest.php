<?php

namespace Tests\Unit\Actions\Corpus\CatalogueWork;

use App\Actions\Corpus\CatalogueWork\SyncToWork;
use App\Models\Corpus\CatalogueWork;
use App\Models\Corpus\CatalogueWorkExpression;
use Tests\TestCase;

class SyncToWorkTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testHandleSyncToWork(): void
    {
        $cWork = CatalogueWork::factory()->create(['start_url' => 'http://example.com']);
        $expression = CatalogueWorkExpression::factory()->for($cWork)->create();
        $work = app(SyncToWork::class)->handle($cWork);

        $this->assertNotNull($work);
        $this->assertSame($cWork->title, $work->title);
        $this->assertSame($cWork->title_translation, $work->title_translation);
        $this->assertSame($cWork->work_number, $work->work_number);
        $this->assertSame($cWork->work_type, $work->work_type);
        $this->assertSame($cWork->work_date->format('Y-m-d'), $work->work_date->format('Y-m-d'));
        $this->assertSame($cWork->language_code, $work->language_code);
        $this->assertSame($cWork->start_url, $work->source_url);
        $this->assertSame($cWork->publication_document_number, $work->notice_number);
        $this->assertSame($cWork->primary_location_id, $work->primary_location_id);
        $this->assertSame($cWork->source_unique_id, $work->source_unique_id);
        $this->assertSame($cWork->publication_number, $work->gazette_number);
        $this->assertSame($cWork->effective_date->format('Y-m-d'), $work->effective_date->format('Y-m-d'));

        $this->assertTrue($work->expressions->isNotEmpty());
        $this->assertSame($expression->start_date, $cWork->expressions->first()->start_date);
        $this->assertTrue($work->workReference->refresh()->locations->contains($work->primaryLocation));
    }
}
