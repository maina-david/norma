<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperReferenceTitleText
 */
class ReferenceTitleText extends AbstractModel
{
    protected $table = 'reference_title_texts';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'reference_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public const CREATED_AT = null;
}
