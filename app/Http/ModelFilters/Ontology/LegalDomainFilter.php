<?php

namespace App\Http\ModelFilters\Ontology;

use App\Traits\UsesArchivedModelFilter;
use EloquentFilter\ModelFilter;

/**
 * @method static LegalDomainFilter titleLike(string $search)
 */
class LegalDomainFilter extends ModelFilter
{
    use UsesArchivedModelFilter;

    /**
     * @param string $search
     *
     * @return LegalDomainFilter
     */
    public function search(string $search): LegalDomainFilter
    {
        /** @var LegalDomainFilter */
        return $this->titleLike($search);
    }
}
