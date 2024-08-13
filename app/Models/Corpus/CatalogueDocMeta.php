<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperCatalogueDocMeta
 */
class CatalogueDocMeta extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'catalogue_doc_meta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'catalogue_doc_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /** @var array<string, string> */
    protected $casts = [
        'doc_meta' => 'array',
    ];

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    public function catalogueDoc(): BelongsTo
    {
        return $this->belongsTo(CatalogueDoc::class, 'catalogue_doc_id');
    }
}
