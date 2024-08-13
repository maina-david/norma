<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperDocumentLegalDomain
 */
class DocumentLegalDomain extends Pivot
{
    /** @var string */
    protected $table = 'librarian_document_legal_domain';
}
