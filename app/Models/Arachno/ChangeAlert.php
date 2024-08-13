<?php

namespace App\Models\Arachno;

use App\Models\AbstractModel;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Work;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperChangeAlert
 */
class ChangeAlert extends AbstractModel
{
    /** @var array<string,string> */
    protected $casts = [
        'payload' => 'array',
    ];

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
        return $this->belongsTo(Work::class, 'work_id');
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
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'source_id');
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
}
