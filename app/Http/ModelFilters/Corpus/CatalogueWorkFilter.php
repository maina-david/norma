<?php

namespace App\Http\ModelFilters\Corpus;

use App\Models\Geonames\Location;
use EloquentFilter\ModelFilter;

/**
 * @method static CatalogueWorkFilter search(string $search)
 * @method static CatalogueWorkFilter titleLike(string $search)
 * @method static CatalogueWorkFilter jurisdiction(int $jurisdiction)
 * @method static CatalogueWorkFilter source(int $source)
 */
class CatalogueWorkFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return CatalogueWorkFilter
     */
    public function search(string $search): CatalogueWorkFilter
    {
        /** @var CatalogueWorkFilter */
        return $this->where(fn ($query) => $query->titleLike($search)->orWhere('start_url', 'like', "%{$search}%"));
    }

    /**
     * @param int $jurisdiction
     *
     * @return CatalogueWorkFilter
     */
    public function jurisdiction(int $jurisdiction): CatalogueWorkFilter
    {
        /** @var Location */
        $location = Location::find($jurisdiction);

        /** @var CatalogueWorkFilter */
        return $this->whereRelation('primaryLocation', fn ($q) => $q->isOrHasAncestor($location));
    }

    /**
     * @param int $source
     *
     * @return CatalogueWorkFilter
     */
    public function source(int $source): CatalogueWorkFilter
    {
        /** @var CatalogueWorkFilter */
        return $this->where('source_id', $source);
    }
}
