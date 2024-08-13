<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Enums\Corpus\ReferenceType;
use App\Jobs\Corpus\UpdateWorkExpressionWordCount;
use App\Jobs\Middleware\Debounce;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceSelector;
use App\Models\Corpus\WorkExpression;
use App\Services\Corpus\VolumeHighlighter;
use App\Services\Storage\WorkStorageProcessor;
use Cache;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ToCContentControllerTest extends TestCase
{
    public function testGettingContent(): void
    {
        Storage::fake();

        $disk = Storage::disk(WorkStorageProcessor::disk());

        $this->signIn($this->collaborateSuperUser());

        /** @var WorkStorageProcessor $processor */
        $processor = $this->app->make(WorkStorageProcessor::class);

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create(['volume' => 2]);
        $expression->work->update(['active_work_expression_id' => $expression->id]);

        $reference = Reference::factory()->create([
            'type' => ReferenceType::citation()->value,
            'work_id' => $expression->work_id,
        ]);

        ReferenceSelector::factory()->for($reference)->create(['selectors' => [[]]]);

        $this->setExpression($expression);

        $this->assertNull($expression->refresh()->converted_document_id);

        Debounce::fake(false);

        $content = '<h1>Awesome Text!</h1><span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>';

        UpdateWorkExpressionWordCount::fakeHighlighter([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['count' => 5])),
        ]);

        VolumeHighlighter::fakeHighlighter([
            new Response(200, ['Content-Type' => 'text/html'], '<h1>Awesome Text!</h1>'),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([$content])),
            new Response(200, ['Content-Type' => 'text/html'], '<span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>'),
        ]);

        $this->assertNull($reference->refresh()->htmlContent?->cached_content);

        // missing the debounce key
        $this->assertTrue(Cache::missing("word_count_{$expression->id}"));

        $this->putContent(route('collaborate.toc.content.update', ['expression' => $expression->id, 'volume' => 1]), $content)
            ->assertSuccessful();

        // job has been booked but not run
        $this->assertTrue(Cache::has("word_count_{$expression->id}"));

        $this->assertSame(0, $expression->refresh()->word_count);
        $this->assertNull($reference->refresh()->htmlContent?->cached_content);
        $this->assertNotNull($expression->refresh()->converted_document_id);

        $this->putContent(route('collaborate.toc.content.update', ['expression' => $expression->id, 'volume' => 1]), $content)
            ->assertSuccessful();

        $this->assertSame(0, $expression->refresh()->word_count);
        $this->assertNull($reference->refresh()->htmlContent?->cached_content);
        $this->assertNotNull($expression->refresh()->converted_document_id);

        $this->travelTo(now()->addSeconds(350));

        // job time has elapsed
        $this->putContent(route('collaborate.toc.content.update', ['expression' => $expression->id, 'volume' => 1]), $content)
            ->assertSuccessful();

        $this->assertSame(5, $expression->refresh()->word_count);
        $this->assertSame($content, $reference->refresh()->htmlContent?->refresh()->cached_content);
        $this->assertNotNull($expression->refresh()->converted_document_id);

        VolumeHighlighter::fakeHighlighter([
            new Response(200, ['Content-Type' => 'text/html'], '<h1>Awesome Text!</h1>'),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([$content])),
            new Response(200, ['Content-Type' => 'text/html'], '<span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>'),
        ]);

        $this->putContent(route('collaborate.toc.content.update', ['expression' => $expression->id, 'volume' => 1]), $content . '<p>Great</p>')
            ->assertSuccessful();

        $this->travelTo(now()->addSeconds(350));

        $this->putContent(route('collaborate.toc.content.update', ['expression' => $expression->id, 'volume' => 1]), $content . '<p>Great</p>')
            ->assertSuccessful();

        $filename = $expression->convertedDocument->path;

        $this->assertTrue($disk->put($filename, '<h1>Awesome Text!</h1><span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>'));

        $path = sprintf('%s/html/volume_%d.html', $processor->expressionPath($expression), 3);

        $this->assertTrue($disk->put($path, '<h1>Awesome Text Again Again and Again!</h1>'));

        $this->assertTrue($processor->exists($path));

        $this->setExpression($expression);

        $this->json('GET', route('collaborate.toc.content.volume.show', ['expression' => $expression->id, 'volume' => 1]))
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Awesome Text!")]]');
    }
}
