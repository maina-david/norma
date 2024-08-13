<?php

namespace App\Models\Compilation;

use App\Models\AbstractModel;
use App\Models\Compilation\Pivots\RequirementCollectionClosure;
use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\LibryoRequirementsCollection;
use App\Models\Geonames\LocationType;
use App\Models\Traits\HasClosureTable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperRequirementsCollection
 */
class RequirementsCollection extends AbstractModel
{
    use HasClosureTable;

    protected $table = 'corpus_locations';

    public function getClosureTableClass(): string
    {
        return RequirementCollectionClosure::class;
    }

    public function closureTableUsesPositions(): bool
    {
        return false;
    }

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
    public function type(): BelongsTo
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    /**
     * Alias for type, here for legacy purposes.
     *
     * @return BelongsTo
     */
    public function locationType(): BelongsTo
    {
        return $this->type();
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(static::class, 'location_country_id');
    }

    public function libryos(): BelongsToMany
    {
        return $this->belongsToMany(Libryo::class, (new LibryoRequirementsCollection())->getTable(), 'collection_id', 'place_id')
            ->using(LibryoRequirementsCollection::class);
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
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'alternate_names' => $this->alternate_names,
        ];
    }
}
