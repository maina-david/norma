<?php

namespace App\Http\Controllers\Assess\My\Traits;

use App\Enums\Assess\ResponseStatus;

trait AvailableActions
{
    /**
     * @return array<string>
     */
    public function getAvailableActions(): array
    {
        return [
            'respond-' . ResponseStatus::yes()->value,
            'respond-' . ResponseStatus::no()->value,
            'respond-' . ResponseStatus::notApplicable()->value,
            'respond-' . ResponseStatus::notAssessed()->value,
            'respond-unchanged',
        ];
    }
}
