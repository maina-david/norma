<?php

namespace Tests\Unit\Stores\Corpus;

use App\Services\Arachno\Parse\PdfExtractor;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class ContentResourceStoreTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testStoreAndGetContent()
    {
        $disk = app(ContentResourceStore::class)->getDiskName();
        Storage::fake($disk);

        $content = 'abcdefg';
        $hash = sha1($content);

        app(ContentResourceStore::class)->put($content);
        $path = app(ContentResourceStore::class)->getPath($hash);

        Storage::disk($disk)->assertExists($path);

        $newContent = app(ContentResourceStore::class)->get($path);
        $this->assertEquals($content, $newContent);
    }

    public function testStorePlainText(): void
    {
        $disk = app(ContentResourceStore::class)->getDiskName();
        Storage::fake($disk);

        $text = 'abcdefg';
        $content = '<p>' . $text . '</p>';
        $resource = app(ContentResourceStore::class)->storeResource($content, 'text/html');
        $this->assertNotNull($resource->text_content_resource_id);

        $this->partialMock(PdfExtractor::class, function (MockInterface $mock) use ($text) {
            $mock->shouldReceive('getText')->andReturn($text);
        });
        $content = 'fake pdf content';
        $resource = app(ContentResourceStore::class)->storeResource($content, 'application/pdf');
        $this->assertNotNull($resource->text_content_resource_id);
    }
}
