<?php

namespace App\Models\Requirements;

use App\Contracts\Translation\HasTranslations;
use App\Models\AbstractModel;
use App\Models\Lookups\Translation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperSummary
 */
class Summary extends AbstractModel implements HasTranslations, Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'reference_summaries';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'reference_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'summary_id');
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
