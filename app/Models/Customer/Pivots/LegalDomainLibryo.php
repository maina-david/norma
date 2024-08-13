<?php

namespace App\Models\Customer\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalDomainLibryo
 */
class LegalDomainLibryo extends Pivot
{
    protected $table = 'legal_domain_place';
}
