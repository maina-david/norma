<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use App\Models\Storage\CorpusDocument;
use App\Services\Storage\WorkStorageProcessor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @mixin IdeHelperCatalogueWorkExpression
 */
class CatalogueWorkExpression extends AbstractModel
{
    /** @var string */
    protected $table = 'corpus_catalogue_work_expressions';

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Mutators
    |--------------------------------------------------------------------------

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(CorpusDocument::class, 'source_document_id');
    }

    /**
     * @return BelongsTo
     */
    public function convertedDocument(): BelongsTo
    {
        return $this->belongsTo(CorpusDocument::class, 'converted_document_id');
    }

    /**
     * @return BelongsTo
     */
    public function catalogueWork(): BelongsTo
    {
        return $this->belongsTo(CatalogueWork::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the contents the given type.
     *
     * @param string $type
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return string|null
     */
    protected function getContent(string $type): ?string
    {
        ini_set('memory_limit', '512M');

        $repo = app(WorkStorageProcessor::class);

        if ($this->{$type} && $repo->exists($this->{$type}->path)) {
            return $repo->get($this->{$type}->path);
        }

        return null;
    }

    /**
     * Get the converted document.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return string|null
     */
    public function getConvertedDocument(): ?string
    {
        return $this->getContent('convertedDocument');
    }

    /**
     * Get the source document.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return string|null
     */
    public function getSourceDocument(): ?string
    {
        return $this->getContent('sourceDocument');
    }

    /**
     * Get the converted document if present.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return string|null
     */
    public function getDocument(): ?string
    {
        return $this->convertedDocument ? $this->getConvertedDocument() : $this->getSourceDocument();
    }
}
