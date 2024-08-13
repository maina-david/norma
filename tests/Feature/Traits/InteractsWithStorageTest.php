<?php

namespace Tests\Feature\Traits;

use App\Enums\Storage\FileType;
use App\Traits\InteractsWithStorage;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InteractsWithStorageTest extends TestCase
{
    /**
     * Create a class that can be saved.
     */
    protected function createTestClass(UploadedFile $file): object
    {
        return new class($file) {
            use InteractsWithStorage;

            protected ?string $path = null;

            protected array $headers = [];

            public function __construct(protected UploadedFile $file)
            {
            }

            /**
             * Get the extra headers to send back when downloading/streaming.
             *
             * @return array<string, mixed>
             */
            protected function fileHeaders(): array
            {
                return $this->headers;
            }

            public function addHeader($key, $value): self
            {
                $this->headers[$key] = $value;

                return $this;
            }

            public function path(): string
            {
                return $this->path;
            }

            protected function filePath(): string
            {
                return $this->path;
            }

            /**
             * Get the name of the file.
             *
             * @return string
             */
            protected function fileName(): string
            {
                return $this->file->getClientOriginalName();
            }

            /**
             * Get the mime type of the file.
             *
             * @return string
             */
            protected function fileMimeType(): string
            {
                return $this->file->getClientMimeType();
            }

            /**
             * Handle the changes required after the file has been saved in storage.
             *
             * @param UploadedFile $file
             * @param string       $path
             *
             * @return void
             */
            protected function fileSaved(UploadedFile $file, string $path): void
            {
                $this->path = $path;
            }

            /**
             * Return the FileType of this file.
             *
             * @return FileType
             */
            protected function getFileType(): FileType
            {
                return FileType::collaborators();
            }
        };
    }

    /**
     * Get the directory where the file will be saved.
     *
     * @return string
     */
    protected function getSavedDirectory(): string
    {
        $type = FileType::collaborators()->value;

        return config("filesystems.filetypes.{$type}.path");
    }

    /**
     * @return void
     */
    public function testItSavesAFile(): void
    {
        Storage::fake();

        $this->assertEmpty(Storage::files($this->getSavedDirectory()));

        $uploaded = UploadedFile::fake()->image('save.jpg');

        $file = $this->createTestClass($uploaded);

        $file->saveFile($uploaded);

        $this->assertCount(1, Storage::files($this->getSavedDirectory()));
    }

    protected function streamingTests(bool $stream): void
    {
        Storage::fake();

        $this->assertEmpty(Storage::files($this->getSavedDirectory()));

        $uploaded = UploadedFile::fake()->image('stream.jpg');

        $file = $this->createTestClass($uploaded);

        $saved = $file->saveFile($uploaded);

        /** @var Response $response */
        $response = $stream ? $saved->stream() : $saved->download();
        $disposition = $stream ? 'inline' : 'attachment';

        $this->assertSame("{$disposition}; filename=" . $uploaded->getClientOriginalName(), $response->headers->get('Content-Disposition'));
        $this->assertSame($uploaded->getClientMimeType(), $response->headers->get('Content-Type'));
        $this->assertSame($uploaded->getContent(), $response->content());
    }

    /**
     * @return void
     */
    public function testItStreamsAFile(): void
    {
        $this->streamingTests(true);
    }

    /**
     * @return void
     */
    public function testItDownloadsAFile(): void
    {
        $this->streamingTests(false);
    }

    /**
     * @return void
     */
    public function testItAddsExtraHeaders(): void
    {
        Storage::fake();

        $uploaded = UploadedFile::fake()->image('stream.jpg');
        $file = $this->createTestClass($uploaded);
        $saved = $file->saveFile($uploaded);
        $saved->addHeader('Who', 'ZebraCorn!');

        /** @var Response $response */
        $response = $saved->stream();

        $this->assertSame('ZebraCorn!', $response->headers->get('Who'));
    }
}
