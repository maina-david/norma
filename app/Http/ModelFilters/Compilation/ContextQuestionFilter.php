<?php

namespace App\Http\ModelFilters\Compilation;

use App\Traits\UsesArchivedModelFilter;
use EloquentFilter\ModelFilter;

/**
 * @method static ContextQuestionFilter questionLike(string $title)
 */
class ContextQuestionFilter extends ModelFilter
{
    use UsesArchivedModelFilter;

    /**
     * @param string $search
     *
     * @return ContextQuestionFilter
     */
    public function search(string $search): self
    {
        /** @var self */
        return $this->where(function ($builder) use ($search) {
            $builder->questionLike($search)
                ->orWhereRelation('descriptions', 'description', 'like', "%{$search}%");
        });
    }

    /**
     * @param array<int, int> $values
     *
     * @return ContextQuestionFilter
     */
    public function categories(array $values): self
    {
        /** @var self */
        return $this->whereHas('categories.ancestorsWithSelf', function ($builder) use ($values) {
            $builder->whereKey($values);
        });
    }
}
