<?php

namespace App\Http\ModelFilters\Corpus;

use App\Models\Geonames\Pivots\LocationLocation;
use EloquentFilter\ModelFilter;

/**
 * @method static DocFilter search(string $search)
 * @method static DocFilter titleLike(string $search)
 * @method static DocFilter source(array $source)
 * @method static DocFilter sourceCategories(array $sourceCategories)
 */
class CatalogueDocFilter extends ModelFilter
{
    //    /**
    //     * @param string $search
    //     *
    //     * @return self
    //     */
    //    public function search(string $search): self
    //    {
    //        /** @var CatalogueDocFilter */
    //        return $this->where(function ($q) use ($search) {
    //            $q->titleLike($search)
    //                ->orWhere('title_translation', 'like', '%' . $search . '%')
    //                ->orWhere('source_unique_id', 'like', '%' . $search . '%');
    //        });
    //    }

    /**
     * @param int $source
     *
     * @return self
     */
    public function source(int $source): self
    {
        /** @var CatalogueDocFilter */
        return $this->where('source_id', $source);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param array<int, int> $sources
     *
     * @return self
     */
    public function sources(array $sources): self
    {
        /** @var CatalogueDocFilter */
        return $this->whereIn('source_id', $sources);
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function fetched(string $value): self
    {
        /** @var CatalogueDocFilter */
        return $this->whereHas('docs');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function linked(string $value): self
    {
        /** @var CatalogueDocFilter */
        return $this->whereHas('work');
    }

    /**
     * @param string $value
     *
     * @return self
     */
    public function failedFetch(string $value): self
    {
        /** @var CatalogueDocFilter */
        return $this->whereNotNull('fetch_started_at')->where('fetch_started_at', '<', now()->subDay());
    }

    /**
     * @param array<int> $sourceCategories
     *
     * @return self
     */
    public function sourceCategories(array $sourceCategories): self
    {
        /** @var CatalogueDocFilter */
        return $this->whereRelation('sourceCategories', fn ($q) => $q->whereKey($sourceCategories));
    }

    /**
     * @param int $location
     *
     * @codeCoverageIgnore
     *
     * @return self
     */
    public function jurisdiction(int $location): self
    {
        $subQuery = LocationLocation::where('ancestor', $location)->select('descendant');

        /** @var CatalogueDocFilter */
        return $this->whereHas('primaryLocation', fn ($q) => $q->whereIn('id', $subQuery))
            ->orWhereHas('source.locations', fn ($q) => $q->whereIn('id', $subQuery));
    }
}
