<?php

namespace App\Http\ModelFilters\Collaborators;

class CollaboratorApplicationFilter extends CollaboratorFilter
{
    /**
     * @param int $value
     *
     * @codeCoverageIgnore
     *
     * @return self
     */
    public function status(int $value): self
    {
        /** @var static */
        return $this->where('application_state', $value);
    }

    /**
     * @param int $value
     *
     * @codeCoverageIgnore
     *
     * @return self
     */
    public function stage(int $value): self
    {
        /** @var static */
        return $this->where('stage', $value);
    }

    /**
     * Model filter for residency.
     *
     * @param string $query
     *
     * @return self
     */
    public function residency(string $query): self
    {
        /** @var static */
        return $this->whereRelation('profile', 'current_residency', $query);
    }

    /**
     * Model filter for residency.
     *
     * @param string $query
     *
     * @return self
     */
    public function nationality(string $query): self
    {
        /** @var static */
        return $this->whereRelation('profile', 'nationality', $query);
    }
}
