<?php

namespace App\Services\Storage;

use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Storage\CorpusDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkStorageProcessor
{
    /**
     * Get the disk to use to store the documents.
     *
     * @return string
     */
    public static function disk(): string
    {
        return sprintf('%s-documents', config('filesystems.default'));
    }

    /**
     * Get the path to the work.
     *
     * @param Work $work
     *
     * @return string
     */
    public function workPath(Work $work): string
    {
        return sprintf('/%s/%d/%d', trim(config('filesystems.paths.works'), '/'), $work->source_id, $work->id);
    }

    /**
     * Get the path to the expression.
     *
     * @param WorkExpression $expression
     *
     * @return string
     */
    public function expressionPath(WorkExpression $expression): string
    {
        $workPath = $this->workPath($expression->work);

        return sprintf('%s/%d', $workPath, $expression->id);
    }

    /**
     * Get the path to the expression.
     *
     * @param WorkExpression $expression
     *
     * @return string
     */
    public function expressionPathSource(WorkExpression $expression): string
    {
        $exprPath = $this->expressionPath($expression);

        return sprintf('%s/source', $exprPath);
    }

    /**
     * Create the work expression directory.
     *
     * @param WorkExpression $expression
     *
     * @return void
     */
    public function createWorkExpressionDirectory(WorkExpression $expression): void
    {
        $expressionPath = $this->expressionPath($expression);
        Storage::disk(static::disk())->makeDirectory($expressionPath);
        Storage::disk(static::disk())->makeDirectory($expressionPath . '/source/images');
        Storage::disk(static::disk())->makeDirectory($expressionPath . '/html');
    }

    /**
     * Get the cached content.
     *
     * @param Work $work
     * @param int  $volume
     *
     * @return string|null
     */
    public function getCachedWorkVolume(Work $work, int $volume): ?string
    {
        $path = $this->workPath($work);
        $path = "{$path}/cache/volume_{$volume}.html";

        return Storage::disk(static::disk())->exists($path)
            ? Storage::disk(static::disk())->get($path)
            : null;
    }

    /**
     * Get the source document for the given expression.
     *
     * @param Work        $work
     * @param string|null $filename
     *
     * @return string|null
     */
    public function getSourceDocument(Work $work, ?string $filename = null): ?string
    {
        $work->load(['activeExpression.sourceDocument']);
        $expression = $work->getCurrentExpression();

        if (!$expression) {
            return null;
        }

        $driver = Storage::disk(static::disk());

        if ($filename) {
            $filePath = $this->getExpressionFilePath($expression, $filename);

            return $filePath ? $driver->get($filePath) : null;
        }

        if ($expression->sourceDocument?->path && $driver->exists($expression->sourceDocument->path)) {
            return $driver->get($expression->sourceDocument->path);
        }

        if ($fallback = $this->getExpressionFilePath($expression)) {
            return $driver->get($fallback);
        }

        return null;
    }

    /**
     * Get the valid source document for the given expression. Fall back to volume_1.html.
     *
     * @param WorkExpression $expression
     * @param string         $file
     *
     * @return string|null
     */
    protected function getExpressionFilePath(WorkExpression $expression, string $file = 'volume_1.html'): ?string
    {
        $file = trim($file, '/');
        $driver = Storage::disk(static::disk());

        $paths = [
            $this->expressionPathSource($expression) . DIRECTORY_SEPARATOR . $file,
            $this->expressionPath($expression) . DIRECTORY_SEPARATOR . $file,
        ];

        foreach ($paths as $path) {
            if ($driver->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Check if the item exists in the path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return Storage::disk(static::disk())->exists($path);
    }

    /**
     * Get the document content.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function get(string $path): ?string
    {
        return Storage::disk(static::disk())->get($path);
    }

    /**
     * Cache the highlighted work volume.
     *
     * @param Work   $work
     * @param int    $volume
     * @param string $content
     *
     * @return void
     */
    public function storeCachedWorkVolume(Work $work, int $volume, string $content): void
    {
        $path = $this->workPath($work);

        Storage::disk(static::disk())->makeDirectory("{$path}/cache");

        $path = "{$path}/cache/volume_{$volume}.html";

        Storage::disk(static::disk())->put($path, $content);
    }

    /**
     * Create a new document from content and return the path.
     *
     * @param WorkExpression      $expression
     * @param string              $content
     * @param string|null         $filename
     * @param CorpusDocument|null $document
     * @param string|null         $mime
     * @param string              $type
     * @param bool                $keepName
     *
     * @return CorpusDocument
     */
    public function documentFromContent(
        WorkExpression $expression,
        string $content,
        ?string $filename = null,
        ?CorpusDocument $document = null,
        ?string $mime = null,
        string $type = 'source',
        bool $keepName = false
    ): CorpusDocument {
        /** @var resource $file */
        $file = tmpfile();

        fwrite($file, $content);

        $filename = $filename ?? Str::random();

        /** @var array<string, mixed> $meta */
        $meta = stream_get_meta_data($file);

        $uploaded = new UploadedFile($meta['uri'], $filename, $mime);

        $created = $this->createDocument($expression, $uploaded, $document, $type, $keepName);

        fclose($file);

        return $created;
    }

    /**
     * Create a new document and return the path.
     *
     * @param WorkExpression      $expression
     * @param UploadedFile        $file
     * @param CorpusDocument|null $document
     * @param bool                $keepName
     * @param string              $type
     *
     * @return CorpusDocument
     */
    public function createDocument(WorkExpression $expression, UploadedFile $file, ?CorpusDocument $document = null, string $type = 'source', bool $keepName = false): CorpusDocument
    {
        $this->createWorkExpressionDirectory($expression);

        $directory = sprintf('%s/%s', $this->expressionPath($expression), $type);

        $filename = $keepName
            ? Storage::disk(static::disk())->putFileAs($directory, $file, $file->getClientOriginalName())
            // @codeCoverageIgnoreStart
            : Storage::disk(static::disk())->putFile($directory, $file);
        // @codeCoverageIgnoreEnd

        $document = $document ?: new CorpusDocument();

        $document->fill([
            'name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'path' => $filename,
        ]);

        $document->save();

        return $document;
    }

    /**
     * Get the files for the given work.
     *
     * @param Work $work
     *
     * @return Collection
     */
    public function getWorkFile(Work $work): Collection
    {
        $directory = "{$work->source_id}/{$work->id}";
        $disk = config('filesystems.default-images');

        return collect(Storage::disk($disk)->files($directory))
            ->map(function ($file) use ($disk) {
                /** @var string $file */
                return Storage::disk($disk)->url($file);
            });
    }

    /**
     * Store the provided image in the image work directory.
     *
     * @param Work         $work
     * @param UploadedFile $file
     *
     * @return string
     */
    public function storeWorkFile(Work $work, UploadedFile $file): string
    {
        $fileName = str_replace([',', '/', '\\', ':'], '', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $directory = "{$work->source_id}/{$work->id}";
        $path = "{$directory}/{$fileName}.{$extension}";
        $disk = config('filesystems.default-images');

        // make a directory if it doesn't exist
        Storage::disk($disk)->makeDirectory($directory);
        Storage::disk($disk)->put($path, $file->getContent());

        return $fileName;
    }

    /**
     * Delete the given work file.
     *
     * @param Work   $work
     * @param string $filename
     *
     * @return void
     */
    public function deleteWorkFile(Work $work, string $filename): void
    {
        $filename = "{$work->source_id}/{$work->id}/{$filename}";
        $disk = config('filesystems.default-images');

        if (Storage::disk($disk)->exists($filename)) {
            Storage::disk($disk)->delete($filename);
        }
    }
}
