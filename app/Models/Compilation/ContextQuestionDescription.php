<?php

namespace App\Models\Compilation;

use App\Models\AbstractModel;
use App\Models\Customer\Libryo;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperContextQuestionDescription
 */
class ContextQuestionDescription extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = 'corpus_context_question_descriptions';

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
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * @return BelongsTo
     */
    public function contextQuestion(): BelongsTo
    {
        return $this->belongsTo(ContextQuestion::class, 'context_question_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     * @param Libryo  $libryo
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $builder, Libryo $libryo): Builder
    {
        if (!$libryo->location) {
            return $builder;
        }

        return $builder->where(function ($query) use ($libryo) {
            $query->where('location_id', $libryo->location->location_country_id)
                ->orWhereNull('location_id')
                ->orderBy('location_id', 'DESC');
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */
}
