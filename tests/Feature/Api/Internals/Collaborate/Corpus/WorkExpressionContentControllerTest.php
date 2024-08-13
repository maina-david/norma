<?php

namespace Tests\Feature\Api\Internals\Collaborate\Corpus;

use App\Models\Corpus\WorkExpression;
use App\Services\Corpus\WorkExpressionContentManager;
use App\Services\Storage\WorkStorageProcessor;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkExpressionContentControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testGettingContent(): void
    {
        Storage::fake();

        $this->signIn($this->collaborateSuperUser());

        $disk = WorkStorageProcessor::disk();
        $drive = Storage::disk($disk);

        /** @var WorkStorageProcessor $processor */
        $processor = $this->app->make(WorkStorageProcessor::class);

        $content = '<h1>Awesome Text!</h1><span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>';

        $manager = new WorkExpressionContentManager();

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create(['volume' => 2]);

        $this->assertNull($expression->refresh()->converted_document_id);

        $manager->saveVolume($expression, 2, $content);

        $this->assertNotNull($expression->refresh()->converted_document_id);

        $filename = $expression->convertedDocument->path;

        $this->assertTrue($drive->put($filename, '<h1>Awesome Text!</h1><span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>'));

        $path = sprintf('%s/html/volume_%d.html', $processor->expressionPath($expression), 3);

        $this->assertTrue($drive->put($path, '<h1>Awesome Text Again Again and Again!</h1>'));

        $this->assertTrue($processor->exists($path));

        $this->getJson(route('api.collaborate.work-expressions.content.show', ['expression' => $expression, 'page' => 1]))
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Awesome Text!")]]');

        $this->getJson(route('api.collaborate.work-expressions.content.show', ['expression' => $expression, 'page' => 2]))
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Awesome Text Again!")]]');

        $expression->update(['volume' => 3]);

        $this->getJson(route('api.collaborate.work-expressions.content.show', ['expression' => $expression, 'page' => 3]))
            ->assertSuccessful();

        $expression->work->update(['source_url' => 'https://example.com']);
        $this->getJson(route('api.collaborate.work-expressions.content.show', ['expression' => $expression, 'page' => 4]))
            ->assertSuccessful()
            ->assertSee('There is no preview available for this document.');

        $expression->update([
            'converted_document_id' => null,
            'volume' => 4,
        ]);

        $this->getJson(route('api.collaborate.work-expressions.content.show', ['expression' => $expression, 'page' => 3]))
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Awesome Text Again Again and Again!")]]');

        $this->assertTrue($this->app->make(WorkStorageProcessor::class)->exists($filename));

        $drive->delete($filename);

        $this->assertFalse($this->app->make(WorkStorageProcessor::class)->exists($filename));

        $this->getJson(route('api.collaborate.work-expressions.content.show', ['expression' => $expression]))
            ->assertSuccessful()
            ->assertSee('There is no preview available for this document.');
    }
}
