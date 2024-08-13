<?php

namespace App\Http\ModelFilters\Workflows;

use EloquentFilter\ModelFilter;

/**
 * @method static ProjectFilter search(string $search)
 * @method static ProjectFilter jurisdiction(string $location)
 * @method static ProjectFilter organisation(string $organisation)
 * @method static ProjectFilter domains(string[] $domains)
 * @method static ProjectFilter owner(string $owner)
 * @method static ProjectFilter pod(string $pod)
 */
class ProjectFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return self
     */
    public function search(string $search): self
    {
        /** @var static */
        return $this->where(function ($query) use ($search) {
            $query->orWhere('title', 'like', '%' . $search . '%');
            $query->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    /**
     * @param string $location
     *
     * @return self
     */
    public function jurisdiction(string $location): self
    {
        /** @var static */
        return $this->where('location_id', (int) $location);
    }

    /**
     * @param string $owner
     *
     * @return self
     */
    public function owner(string $owner): self
    {
        /** @var static */
        return $this->where('owner_id', (int) $owner);
    }

    /**
     * @param string $organisation
     *
     * @return self
     */
    public function organisation(string $organisation): self
    {
        /** @var static */
        return $this->where('organisation_id', (int) $organisation);
    }

    /**
     * @param string[] $domains
     *
     * @return self
     */
    public function domains(array $domains): self
    {
        if (empty($domains)) {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        /** @var static */
        return $this->whereRelation('legalDomains', fn ($q) => $q->whereIn('id', $domains));
    }

    /**
     * @param string $pod
     *
     * @return self
     */
    public function pod(string $pod): self
    {
        /** @var static */
        return $this->where('production_pod_id', (int) $pod);
    }
}
