<?php

namespace App\Models\Lookups;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperAnnotationSource
 */
class AnnotationSource extends AbstractModel
{
    protected $table = 'annotation_sources';
}
