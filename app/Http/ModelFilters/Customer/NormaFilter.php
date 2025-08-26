<?php

namespace App\Http\ModelFilters\Customer;

use EloquentFilter\ModelFilter;

/**
 * @method static NormaFilter titleLike(string $title)
 * @method static NormaFilter deactivated(string $deactivated)
 * @method static NormaFilter active()
 * @method static NormaFilter inActive()
 * @method static NormaFilter forReferences(array $citations)
 * @method static NormaFilter streams(array $streams)
 */
class NormaFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return NormaFilter
     */
    public function search(string $search): NormaFilter
    {
        return $this->titleLike($search);
    }

    /**
     * @param string $deactivated
     *
     * @return NormaFilter
     */
    public function deactivated(string $deactivated): NormaFilter
    {
        return (bool) $deactivated
            ? $this->inActive()
            : $this->active();
    }

    /**
     * @param array<int> $citations
     *
     * @return NormaFilter
     */
    public function citations(array $citations): NormaFilter
    {
        /** @var NormaFilter */
        return $this->forReferences($citations);
    }

    /**
     * @param array<int> $streams
     *
     * @return NormaFilter
     */
    public function streams(array $streams): NormaFilter
    {
        /** @var NormaFilter */
        return $this->whereKey($streams);
    }
}
