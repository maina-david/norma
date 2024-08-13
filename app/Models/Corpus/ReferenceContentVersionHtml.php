<?php

namespace App\Models\Corpus;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperReferenceContentVersionHtml
 */
class ReferenceContentVersionHtml extends AbstractModel
{
    /** @var string */
    protected $table = 'reference_content_versions_html';

    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'reference_content_version_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
