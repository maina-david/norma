<?php

namespace App\Http\ModelFilters\Storage\My;

use EloquentFilter\ModelFilter;

class FileFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return FileFilter
     */
    public function search(string $search): FileFilter
    {
        // @phpstan-ignore-next-line
        return $this->where(function ($q) use ($search) {
            $joined = str_replace(' ', '', $search);

            $q->titleLike($search)
                ->orWhere('description', 'like', "%{$search}%")
                ->when($joined !== $search, fn ($builder) => $builder->orWhere(fn ($q) => $q->titleLike($joined))->orWhere('description', 'like', "%{$joined}%"));
        });
    }

    /**
     * Filter by locations.
     *
     * @param array<array-key, int> $jurisdiction
     *
     * @return FileFilter
     */
    public function jurisdictions(array $jurisdiction): FileFilter
    {
        // @phpstan-ignore-next-line
        return $this->whereHas('locations', fn ($builder) => $builder->whereKey($jurisdiction));
    }

    /**
     * Filter by domains.
     *
     * @param array<array-key, int> $domains
     *
     * @return FileFilter
     */
    public function domains(array $domains): FileFilter
    {
        // @phpstan-ignore-next-line
        return $this->whereHas('legalDomains', fn ($builder) => $builder->whereKey($domains));
    }
}
