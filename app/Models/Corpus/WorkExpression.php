<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use App\Models\Storage\CorpusDocument;
use App\Services\Corpus\WorkExpressionContentManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperWorkExpression
 */
class WorkExpression extends AbstractModel implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /** @var string */
    protected $table = 'corpus_work_expressions';
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
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /**
     * @return BelongsTo
     */
    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class, 'doc_id');
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
    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(CorpusDocument::class, 'source_document_id');
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
     * Get the contents of the volume.
     *
     * @param int $volume
     *
     * @return string|null
     */
    public function getVolume(int $volume): ?string
    {
        return app(WorkExpressionContentManager::class)->getVolume($this, $volume);
    }

    /**
     * Get the contents of the source document.
     *
     * @return string|null
     */
    public function getSourceDocument(): ?string
    {
        return app(WorkExpressionContentManager::class)->getSourceDocument($this);
    }

    /**
     * Get the cache key to use when saving the generate citations batch.
     *
     * @return string
     */
    public function citationsBatchCacheKey(): string
    {
        /** @var string $key */
        $key = config('cache-keys.corpus.generate_citations.prefix');

        return $key . $this->work_id;
    }
}
