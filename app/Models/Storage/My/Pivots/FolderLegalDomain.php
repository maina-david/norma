<?php

namespace App\Models\Storage\My\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperFolderLegalDomain
 */
class FolderLegalDomain extends Pivot
{
    /** @var string */
    protected $table = 'folder_legal_domain';
}
