<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use App\Models\Ontology\WorkType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperDocMeta
 */
class DocMeta extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'doc_meta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'doc_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class, 'doc_id');
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class, 'work_type_id');
    }

    public function summaryContentResource(): BelongsTo
    {
        return $this->belongsTo(ContentResource::class, 'summary_content_resource_id');
    }
}
