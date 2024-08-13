<?php

namespace App\Http\ModelFilters\Customer;

use EloquentFilter\ModelFilter;

/**
 * @method static LibryoFilter titleLike(string $title)
 * @method static LibryoFilter deactivated(string $deactivated)
 * @method static LibryoFilter active()
 * @method static LibryoFilter inActive()
 * @method static LibryoFilter forReferences(array $citations)
 * @method static LibryoFilter streams(array $streams)
 */
class LibryoFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return LibryoFilter
     */
    public function search(string $search): LibryoFilter
    {
        return $this->titleLike($search);
    }

    /**
     * @param string $deactivated
     *
     * @return LibryoFilter
     */
    public function deactivated(string $deactivated): LibryoFilter
    {
        return (bool) $deactivated
            ? $this->inActive()
            : $this->active();
    }

    /**
     * @param array<int> $citations
     *
     * @return LibryoFilter
     */
    public function citations(array $citations): LibryoFilter
    {
        /** @var LibryoFilter */
        return $this->forReferences($citations);
    }

    /**
     * @param array<int> $streams
     *
     * @return LibryoFilter
     */
    public function streams(array $streams): LibryoFilter
    {
        /** @var LibryoFilter */
        return $this->whereKey($streams);
    }
}
