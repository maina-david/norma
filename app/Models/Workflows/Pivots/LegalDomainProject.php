<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalDomainProject
 */
class LegalDomainProject extends Pivot
{
    /** @var string */
    protected $table = 'legal_domain_librarian_project';
}
