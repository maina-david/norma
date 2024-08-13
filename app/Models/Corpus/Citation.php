<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCitation
 */
class Citation extends AbstractModel
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'corpus_citations';
}
