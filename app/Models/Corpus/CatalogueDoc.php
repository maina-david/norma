<?php

namespace App\Models\Corpus;

use App\Http\ModelFilters\Corpus\CatalogueDocFilter;
use App\Models\AbstractModel;
use App\Models\Arachno\Crawler;
use App\Models\Arachno\Source;
use App\Models\Arachno\SourceCategory;
use App\Models\Corpus\Pivots\CatalogueDocSourceCategory;
use App\Models\Geonames\Location;
use App\Models\Traits\ApiQueryFilterable;
use App\Models\Traits\HasTitleLikeScope;
use App\Models\Traits\UsesUid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Searchable;

/**
 * @mixin IdeHelperCatalogueDoc
 */
class CatalogueDoc extends AbstractModel
{
    use HasTitleLikeScope;
    use UsesUid;
    use Filterable;
    use ApiQueryFilterable;
    use Searchable;

    /** @var array<int, string> */
    protected $hidden = [
        'uid',
    ];

    /** @var array<int, string> */
    protected $appends = [
        'uid_hash',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'post_data' => 'array',
        'last_updated_at' => 'datetime',
        'fetch_started_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function crawler(): BelongsTo
    {
        return $this->belongsTo(Crawler::class);
    }

    public function docs(): HasMany
    {
        return $this->hasMany(Doc::class, 'uid', 'uid');
    }

    public function latestDoc(): HasOne
    {
        return $this->hasOne(Doc::class, 'uid', 'uid')->latestOfMany();
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'uid', 'uid');
    }

    public function sourceCategories(): BelongsToMany
    {
        return $this->belongsToMany(SourceCategory::class, (new CatalogueDocSourceCategory())->getTable(), 'catalogue_doc_id', 'source_category_id')
            ->using(CatalogueDocSourceCategory::class);
    }

    public function docMeta(): HasOne
    {
        return $this->hasOne(CatalogueDocMeta::class, 'catalogue_doc_id');
    }

    /**
     * @return BelongsTo
     */
    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
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
    public function getUidString(): string
    {
        return ($this->source_id ?? '') . '__' . $this->source_unique_id;
    }

    /**
     * @return string
     */
    public function modelFilter(): string
    {
        return $this->provideFilter(CatalogueDocFilter::class);
    }

    /**
     * Get the engine used to index the model.
     *
     * @return \Laravel\Scout\Engines\Engine
     */
    public function searchableUsing(): Engine
    {
        return app(EngineManager::class)->engine('libryo-ai');
    }

    /**
     * {@inheritDoc}
     */
    public function searchableAs(): string
    {
        return 'catalogues';
    }

    /**
     * @return array<mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'source_id' => $this->source_id,
            'title' => $this->title,
            'description' => $this->summary,
            'location_id' => $this->primary_location_id ? [$this->primary_location_id] : [],
            'crawler_id' => $this->crawler_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getSearchWeights(): array
    {
        return [
            'text_weight' => 1,
            'keywords_weight' => 20,
            'description_weight' => 1,
            'text_embedding_weight' => 100,
        ];
    }
}
