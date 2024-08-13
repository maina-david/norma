<?php

namespace App\Models\Compilation\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperLibraryReference
 */
class LibraryReference extends Pivot
{
    /** @var string */
    protected $table = 'library_register_item';
}
