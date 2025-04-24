<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalDomainNorma
 */
class LegalDomainNorma extends Pivot
{
    protected $table = 'legal_domain_place';
}
