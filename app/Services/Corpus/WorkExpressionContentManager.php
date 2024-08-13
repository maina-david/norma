<?php

namespace App\Services\Corpus;

use App\Jobs\Corpus\CacheWorkContent;
use App\Jobs\Corpus\UpdateWorkExpressionWordCount;
use App\Models\Corpus\WorkExpression;
use App\Models\Storage\CorpusDocument;
use App\Services\Storage\WorkStorageProcessor;

class WorkExpressionContentManager
{
    /**
     * Update the config if not running in console.
     *
     * @codeCoverageIgnore
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    protected function updateIniConfig(string $key, string $value): void
    {
        if (!app()->runningInConsole()) {
            ini_set($key, $value);
        }
    }

    /**
     * Get the content of the work expression volume.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     *
     * @return string|null
     */
    public function getVolume(WorkExpression $expression, int $volume): ?string
    {
        if ($expression->volume < $volume) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $content = $this->getVolumeContent($expression, $volume);

        if (!$content || strlen($content) < 5 || $expression->volume === 1) {
            return $content;
        }

        $processor = new WorkStorageProcessor();

        $converted = $expression->convertedDocument && $processor->exists($expression->convertedDocument->path);

        return $this->extractVolume($content, $volume, $expression->volume, $converted);
    }

    /**
     * Get the document content.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     *
     * @return string|null
     */
    public function getVolumeContent(WorkExpression $expression, int $volume): ?string
    {
        $expression->load(['convertedDocument']);

        $this->updateIniConfig('memory_limit', '512M');

        $processor = new WorkStorageProcessor();

        if ($expression->convertedDocument && $processor->exists($expression->convertedDocument->path)) {
            return $processor->get($expression->convertedDocument->path);
        }

        $expressionPath = $processor->expressionPath($expression);

        $content = '';
        $foundVolume = false;

        for ($v = $volume; $v <= $volume; $v++) {
            $filename = sprintf('%s/html/volume_%d.html', $expressionPath, $v);

            if ($processor->exists($filename)) {
                $foundVolume = true;
                $content .= $processor->get($filename);
            }
        }

        if (!$foundVolume) {
            $filename = sprintf('%s/html/volume_1.html', $expressionPath);
            $content .= $processor->exists($filename) ? $processor->get($filename) : '';
        }

        return $content ?: null;
    }

    /**
     * Migrate from using paths to using documents.
     *
     * @param \App\Models\Corpus\WorkExpression $expression
     *
     * @return \App\Models\Storage\CorpusDocument
     */
    public function migrateToDocuments(WorkExpression $expression): CorpusDocument
    {
        $expression->load(['convertedDocument']);

        if ($expression->convertedDocument) {
            return $expression->convertedDocument;
        }

        $this->updateIniConfig('max_execution_time', '300');
        $processor = new WorkStorageProcessor();

        $volume = 1;
        $content = '';

        while (true) {
            if (!$volumeContent = $expression->getVolume($volume)) {
                break;
            }

            if ($volume > 1) {
                $content .= sprintf('<div data-end-of-volume="%d"></div>', $volume - 1);
            }

            $content .= $volumeContent;
            $volume++;
        }

        $document = $processor->documentFromContent($expression, $content, null, null, 'text/html', 'html');

        $expression->converted_document_id = $document->id;
        //        $expression->source_path = sprintf('%s/html/volume_%d.html', $expressionPath, 1);

        $expression->unsetRelation('work');

        $expression->save();

        return $document;
    }

    /**
     * Get the source document content.
     *
     * @param WorkExpression $expression
     *
     * @return string|null
     */
    public function getSourceDocument(WorkExpression $expression): ?string
    {
        $expression->load(['sourceDocument']);

        $this->updateIniConfig('memory_limit', '512M');

        $processor = new WorkStorageProcessor();

        return $expression->sourceDocument && $processor->exists($expression->sourceDocument->path) ? $processor->get($expression->sourceDocument->path) : null;
    }

    /**
     * Extract the volume from the given content.
     *
     * @param string $content
     * @param int    $volume
     * @param int    $lastVolume
     * @param bool   $reading
     *
     * @return string
     */
    public static function extractVolume(string $content, int $volume, int $lastVolume, bool $reading = true): string
    {
        $startPattern = sprintf('/<[^<]+data-end-of-volume=\\\\?"(%d)\\\\?"/', $volume - 1);
        $endPattern = sprintf('/<[^<]+data-end-of-volume=\\\\?"(%d)\\\\?"/', $volume);

        $hasStart = preg_match($startPattern, $content) > 0;

        // The volume is more than 1 but the beginning marker is missing.
        if ($reading && $volume > 1 && !$hasStart) {
            return '';
        }

        // if this is the last volume, we don't expect an end marker, otherwise we do.
        $hasEnd = $lastVolume === $volume || preg_match($endPattern, $content) > 0;

        // it's missing an end marker
        if ($reading && !$hasEnd) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        $pattern = sprintf('/<[^<]+data-end-of-volume="(%d)"/', $volume);

        if (preg_match($pattern, $content) > 0 && $split = preg_split($pattern, $content)) {
            $content = $split[0];
        }

        --$volume;

        $pattern = sprintf('/<[^<]+data-end-of-volume="%d"/', $volume);

        $matches = [];

        if (preg_match($pattern, $content, $matches) < 1) {
            return $content;
        }

        $split = preg_split($pattern, $content);

        if ($split !== false) {
            $content = $split[1];
            $content = $matches[0] . $content;
        }

        return $content;
    }

    /**
     * Persist the content.
     *
     * @param WorkExpression $expression
     * @param int            $volume
     * @param string         $content
     *
     * @return void
     */
    public function saveVolume(WorkExpression $expression, int $volume, string $content): void
    {
        if ($volume > $expression->volume) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $processor = new WorkStorageProcessor();
        $converted = $expression->convertedDocument;

        $validDocument = $converted && $processor->exists($converted->path);
        $filename = "volume_{$volume}.html";

        if ($validDocument) {
            /** @var string $original */
            $original = $processor->get($converted->path);
            $volumeContent = $expression->getVolume($volume) ?? '';

            if ($volumeContent !== $original) {
                // @codeCoverageIgnoreStart
                $content = str_replace($volumeContent, $content, $original);
                // @codeCoverageIgnoreEnd
            }

            $filename = explode(',', $converted->path);
            $filename = $filename[count($filename) - 1];
        }

        $expressionPath = $processor->expressionPath($expression);
        $toUpdate = $processor->exists(sprintf('%s/html/volume_%d.html', $expressionPath, $volume == 1 ? 2 : $volume));
        $complete = $processor->exists(sprintf('%s/html/volume_1.html', $expressionPath));

        if (!$validDocument && !$toUpdate && $complete) {
            // @codeCoverageIgnoreStart
            /** @var string $original */
            $original = $processor->get(sprintf('%s/html/volume_1.html', $expressionPath));

            $volumeContent = self::extractVolume($original, $volume, $expression->volume, false);

            if ($volumeContent !== $original) {
                $content = str_replace($volumeContent, $content, $original);
            }

            $filename = 'volume_1.html';
            // @codeCoverageIgnoreEnd
        }

        $document = $processor->documentFromContent($expression, $content, $filename, $converted, 'text/html', 'html', true);

        $expression->converted_document_id = $document->id;

        $expression->save();

        UpdateWorkExpressionWordCount::dispatch($expression);

        if ($expression->work->active_work_expression_id === $expression->id) {
            CacheWorkContent::dispatch($expression->work);
        }
    }

    /**
     * Get the invalid volume markers.
     *
     * @param WorkExpression $expression
     *
     * @return array<array-key, int>
     */
    public function validateVolumeMarkers(WorkExpression $expression): array
    {
        if ($expression->volume === 1) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $expression->load(['convertedDocument']);
        $processor = new WorkStorageProcessor();

        if (!$expression->convertedDocument || !$processor->exists($expression->convertedDocument->path)) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $this->updateIniConfig('max_execution_time', '120');
        $this->updateIniConfig('memory_limit', '512M');

        $content = $processor->get($expression->convertedDocument->path);

        if (!$content) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        $invalid = [];

        for ($marker = 1; $marker < $expression->volume; $marker++) {
            $startPattern = $marker > 1 ? sprintf(' data-end-of-volume="%d"', $marker - 1) : null;
            $endPattern = sprintf(' data-end-of-volume="%d"', $marker);

            if ($startPattern && !str_contains($content, $startPattern)) {
                $invalid[] = $marker;
            }

            if (!str_contains($content, $endPattern)) {
                $invalid[] = $marker;
            }

            $index = strpos($content, $endPattern);

            if ($index !== false) {
                $content = substr($content, $index - (strlen($endPattern) + 20));
            }
        }

        return collect($invalid)->unique()->values()->toArray();
    }
}
