<?php

namespace App\Http\ModelFilters\Arachno;

use EloquentFilter\ModelFilter;

/**
 * @method static DocFilter search(string $search)
 * @method static DocFilter titleLike(string $search)
 * @method static DocFilter source(array $source)
 */
class CrawlerFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var CrawlerFilter */
        return $this->titleLike($search);
    }

    /**
     * @param int $source
     *
     * @return self
     */
    public function source(int $source): self
    {
        /** @var CrawlerFilter */
        return $this->whereRelation('source', fn ($q) => $q->whereKey($source));
    }
}
