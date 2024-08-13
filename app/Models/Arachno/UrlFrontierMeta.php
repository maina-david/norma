<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUrlFrontierMeta
 */
class UrlFrontierMeta extends AbstractModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'url_frontier_meta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'url_frontier_link_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /** @var array<string, string> */
    protected $casts = [
        'meta' => 'array',
    ];

    /*****************************************************************
    / Model Relationships
    /*****************************************************************/

    public function urlFrontierLink(): BelongsTo
    {
        return $this->belongsTo(UrlFrontierLink::class, 'url_frontier_link_id');
    }
}
