<?php

namespace App\Traits;

trait UsesArchivedModelFilter
{
    /**
     * @param string $archived
     *
     * @return self
     */
    public function archived(string $archived): self
    {
        /** @var self */
        return strtolower($archived) === 'yes' ? $this->whereNotNull('archived_at') : $this->whereNull('archived_at');
    }
}
