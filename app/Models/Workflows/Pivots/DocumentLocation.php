<?php

namespace App\Models\Workflows\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperDocumentLocation
 */
class DocumentLocation extends Pivot
{
    /** @var string */
    protected $table = 'librarian_document_location';
}
