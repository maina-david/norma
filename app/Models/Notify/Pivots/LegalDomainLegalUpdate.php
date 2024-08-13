<?php

namespace App\Models\Notify\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalDomainLegalUpdate
 */
class LegalDomainLegalUpdate extends Pivot
{
    /** @var string */
    protected $table = 'corpus_legal_domain_legal_update';
}
