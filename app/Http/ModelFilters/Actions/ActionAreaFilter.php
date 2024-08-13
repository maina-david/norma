<?php

namespace App\Http\ModelFilters\Actions;

use App\Http\ModelFilters\Assess\AssessmentItemFilter;
use App\Traits\UsesArchivedModelFilter;
use EloquentFilter\ModelFilter;

/**
 * @method static AssessmentItemFilter search(string $search)
 * @method static AssessmentItemFilter searchComponents(string $search)
 * @method static AssessmentItemFilter domains(int $domainIds)
 * @method static AssessmentItemFilter forLegalDomains(array $domainIds)
 */
class ActionAreaFilter extends ModelFilter
{
    use UsesArchivedModelFilter;
}
