<?php

namespace Tests\Feature\Corpus\Collaborate;

use App\Models\Arachno\Source;
use App\Models\Corpus\Doc;
use App\Models\Corpus\WorkExpression;
use App\Models\Storage\CorpusDocument;
use App\Services\Corpus\WorkExpressionContentManager;
use App\Services\Storage\WorkStorageProcessor;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkExpressionContentControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRendersContent(): void
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
        $expression->work->update(['source_url' => 'https://example.com']);

        $this->assertNull($expression->refresh()->converted_document_id);

        $manager->saveVolume($expression, 2, $content);

        $this->assertNotNull($expression->refresh()->converted_document_id);

        $filename = $expression->convertedDocument->path;

        $this->assertTrue($drive->put($filename, '<h1>Awesome Text!</h1><span data-end-of-volume="1"></span><h1>Awesome Text Again!</h1>'));

        $path = sprintf('%s/html/volume_%d.html', $processor->expressionPath($expression), 3);

        $this->assertTrue($drive->put($path, '<h1>Awesome Text Again Again and Again!</h1>'));

        $this->assertTrue($processor->exists($path));

        $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression, 'volume' => 1]), ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Awesome Text!")]]');

        $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression, 'volume' => 2]), ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Awesome Text Again!")]]');

        $expression->update(['volume' => 3]);

        $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression, 'volume' => 3]), ['turbo-frame' => 'frame'])
            ->assertSuccessful();

        $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression, 'volume' => 4]), ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertSee('There is no preview available for this document.');

        $expression->update([
            'converted_document_id' => null,
            'volume' => 4,
        ]);

        $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression, 'volume' => 3]), ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Awesome Text Again Again and Again!")]]');

        $this->assertTrue($this->app->make(WorkStorageProcessor::class)->exists($filename));

        $drive->delete($filename);

        $this->assertFalse($this->app->make(WorkStorageProcessor::class)->exists($filename));

        $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression]), ['turbo-frame' => 'frame'])
            ->assertSuccessful()
            ->assertSee('There is no preview available for this document.');
    }

    public function testStitchingVolumes(): void
    {
        Storage::fake();

        $this->signIn($this->collaborateSuperUser());

        $disk = WorkStorageProcessor::disk();
        $drive = Storage::disk($disk);

        /** @var WorkStorageProcessor $processor */
        $processor = $this->app->make(WorkStorageProcessor::class);

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create(['volume' => 4]);

        for ($vol = 1; $vol <= 4; $vol++) {
            $volumePath = sprintf('%s/html/volume_%d.html', $processor->expressionPath($expression), $vol);
            $this->assertTrue($drive->put($volumePath, "<h1>Awesome Volume {$vol} Content!</h1>"));
        }

        $this->assertNull($expression->refresh()->converted_document_id);

        $volume1 = sprintf('%s/html/volume_1.html', $processor->expressionPath($expression));

        $source = Source::factory()->create();
        $expression->work->update(['source_id' => $source->id]);

        $converted = $expression->refresh()->converted_document_id;

        $this->assertNotNull($converted);
        //        $this->assertSame($volume1, $expression->refresh()->source_path);

        for ($vol = 1; $vol <= 4; $vol++) {
            $text = "Awesome Volume {$vol} Content!";

            $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression, 'volume' => $vol]), ['turbo-frame' => 'frame'])
                ->assertSuccessful()
                ->assertSeeSelector(sprintf('//h1[text()[contains(.,"%s")]]', $text));
        }

        $source = Source::factory()->create();
        $expression->work->update(['source_id' => $source->id]);

        for ($vol = 1; $vol <= 4; $vol++) {
            $text = "Awesome Volume {$vol} Content!";

            $this->get(route('collaborate.work-expressions.volume.show', ['expression' => $expression, 'volume' => $vol]), ['turbo-frame' => 'frame'])
                ->assertSuccessful()
                ->assertSeeSelector(sprintf('//h1[text()[contains(.,"%s")]]', $text));
        }

        $this->assertSame($converted, $expression->refresh()->converted_document_id);
    }

    /**
     * @return void
     */
    public function testItRespondsWithContent(): void
    {
        Storage::fake();

        $this->signIn($this->collaborateSuperUser());

        /** @var WorkStorageProcessor $processor */
        $processor = $this->app->make(WorkStorageProcessor::class);
        $disk = WorkStorageProcessor::disk();
        $drive = Storage::disk($disk);

        $document = CorpusDocument::factory()->create(['mime_type' => 'text/html', 'extension' => 'html']);

        /** @var WorkExpression $expression */
        $expression = WorkExpression::factory()->create(['source_document_id' => $document]);
        $path = $processor->expressionPath($expression);
        $filename = Str::random();

        $content = '<h1>Source Document!</h1>';

        $drive->put("{$path}/{$filename}.html", $content);

        $document->update(['path' => "{$path}/{$filename}.html"]);

        $route = route('collaborate.work-expressions.source.show', ['expression' => $expression]);
        $this->get($route)
            ->assertSuccessful()
            ->assertSeeSelector('//h1[text()[contains(.,"Source Document!")]]');

        // test August model content ie. if ($expression->doc)
        Storage::fake('local');
        $testContent = 'Should show this';
        Storage::disk('local')->put($path, $testContent);
        // test for August content model source document
        $contentResource = app(ContentResourceStore::class)->storeResource($testContent, 'text/plain');

        /** @var Doc */
        $doc = Doc::factory()->create(['first_content_resource_id' => $contentResource->id]);
        $expression->update(['doc_id' => $doc->id]);

        $response = $this->get($route)
            ->assertSuccessful()
            ->assertSee($testContent);
    }
}
