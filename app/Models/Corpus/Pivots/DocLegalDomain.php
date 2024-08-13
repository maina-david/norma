<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperDocLegalDomain
 */
class DocLegalDomain extends Pivot
{
    /** @var string */
    protected $table = 'doc_legal_domain';
}
