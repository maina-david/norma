<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Storage\CorpusDocument;
use App\Services\Storage\WorkStorageProcessor;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkSourceDocumentControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItProvidesTheCorrectDocument(): void
    {
        $processor = (new WorkStorageProcessor());

        Storage::fake(WorkStorageProcessor::disk());

        $this->signIn($this->collaborateSuperUser());

        $work = Work::factory()->create();

        $this->get(route('collaborate.works.source.preview', $work->id))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('There is no preview available for this document.');

        $expression = WorkExpression::factory()->create(['work_id' => $work->id]);

        $work->update(['active_work_expression_id' => $expression->id]);

        $this->get(route('collaborate.works.source.preview', $work->id))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('There is no preview available for this document.');

        $source = $processor->expressionPathSource($expression) . '/volume_1.html';

        Storage::disk(WorkStorageProcessor::disk())->put($source, 'volume_1');

        $this->get(route('collaborate.works.source.preview', $work->id))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertHeader('Content-Length', 8);

        $source = $processor->expressionPathSource($expression) . '/test.html';
        $css = $processor->expressionPathSource($expression) . '/app.css';

        Storage::disk(WorkStorageProcessor::disk())->put($source, 'test');
        Storage::disk(WorkStorageProcessor::disk())->put($css, 'html {}');

        $document = CorpusDocument::factory()->create(['path' => $source, 'mime_type' => 'text/html', 'extension' => 'html']);

        $expression->update(['source_document_id' => $document->id]);

        $this->get(route('collaborate.works.source.preview', $work->id))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertHeader('Content-Length', 4);

        $document->update(['mime_type' => 'application/json', 'extension' => 'json']);

        $this->get(route('collaborate.works.source.preview', $work->id))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Content-Length', 4);

        $this->get(route('collaborate.works.source.preview', ['work' => $work->id, 'file' => 'app.css']))
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'text/css; charset=UTF-8')
            ->assertHeader('Content-Length', 7);
    }
}
