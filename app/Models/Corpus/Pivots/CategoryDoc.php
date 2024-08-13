<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCategoryDoc
 */
class CategoryDoc extends Pivot
{
    /** @var string */
    protected $table = 'category_doc';
}
