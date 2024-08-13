<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLegalDomainReference
 */
class LegalDomainReference extends Pivot
{
    /** @var string */
    protected $table = 'corpus_legal_domain_reference';
}
