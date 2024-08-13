<?php

namespace App\Models\Storage\My\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperFileLegalDomain
 */
class FileLegalDomain extends Pivot
{
    protected $table = 'file_legal_domain';
}
