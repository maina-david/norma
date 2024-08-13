<?php

namespace App\Models\Storage;

use App\Models\AbstractModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCorpusDocument
 */
class CorpusDocument extends AbstractModel
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'corpus_documents';
}
