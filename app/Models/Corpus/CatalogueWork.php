<?php

namespace App\Models\Corpus;

use App\Enums\Application\ApplicationType;
use App\Http\ModelFilters\Corpus\CatalogueWorkFilter;
use App\Models\AbstractModel;
use App\Models\Geonames\Location;
use App\Models\Traits\HasTitleLikeScope;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperCatalogueWork
 */
class CatalogueWork extends AbstractModel
{
    use HasTitleLikeScope;
    use Filterable;

    /** @var string */
    protected $table = 'corpus_catalogue_works';

    /** @var array<string, string> */
    protected $casts = [
        'work_date' => 'date',
        'effective_date' => 'date',
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

    public function work(): HasOne
    {
        return $this->hasOne(Work::class, 'catalogue_work_id');
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    public function expressions(): HasMany
    {
        return $this->hasMany(CatalogueWorkExpression::class, 'catalogue_work_id');
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
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(CatalogueWorkFilter::class);
    }

    /**
     * {@inheritDoc}
     */
    public static function excludeFromCrud(): array
    {
        return [
            ApplicationType::collaborate()->value => ['create', 'update'],
        ];
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'title_translation' => $this->title_translation,
        ];
    }
}
