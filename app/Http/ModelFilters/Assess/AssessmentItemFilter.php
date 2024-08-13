<?php

namespace App\Http\ModelFilters\Assess;

use App\Traits\Bookmarks\UsesBookmarksModelFilter;
use App\Traits\UsesArchivedModelFilter;
use EloquentFilter\ModelFilter;

/**
 * @method static AssessmentItemFilter search(string $search)
 * @method static AssessmentItemFilter searchComponents(string $search)
 * @method static AssessmentItemFilter domains(int $domainIds)
 * @method static AssessmentItemFilter forLegalDomains(array $domainIds)
 */
class AssessmentItemFilter extends ModelFilter
{
    use UsesBookmarksModelFilter;
    use UsesArchivedModelFilter;

    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var self */
        return $this->where(function ($builder) use ($search) {
            $builder->searchComponents($search)
                ->orWhereRelation('descriptions', 'description', 'like', "%{$search}%")
                ->when(is_numeric($search), fn ($query) => $query->orWhere('id', $search));
        });
    }

    /**
     * @param array<int> $domainIds
     *
     * @return self
     */
    public function domains(array $domainIds): self
    {
        return $this->forLegalDomains($domainIds);
    }
}
