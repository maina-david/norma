<?php

namespace App\Models\Corpus\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperCatalogueDocSourceCategory
 */
class CatalogueDocSourceCategory extends Pivot
{
    /** @var string */
    protected $table = 'catalogue_doc_source_category';
}
