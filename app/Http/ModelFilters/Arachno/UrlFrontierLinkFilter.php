<?php

namespace App\Http\ModelFilters\Arachno;

use EloquentFilter\ModelFilter;

/**
 * @method static DocFilter search(string $search)
 * @method static DocFilter source(array $source)
 */
class UrlFrontierLinkFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var UrlFrontierLinkFilter */
        return $this->where('url', 'like', '%' . $search . '%');
    }

    /**
     * @param int $source
     *
     * @return self
     */
    public function source(int $source): self
    {
        /** @var UrlFrontierLinkFilter */
        return $this->whereRelation('crawl.crawler.source', fn ($q) => $q->whereKey($source));
    }
}
