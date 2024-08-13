<?php

namespace App\Http\ModelFilters\Lookups;

use EloquentFilter\ModelFilter;

class CannedResponseFilter extends ModelFilter
{
    public function forField(string $field): CannedResponseFilter
    {
        /** @var CannedResponseFilter */
        return $this->where('for_field', $field);
    }
}
