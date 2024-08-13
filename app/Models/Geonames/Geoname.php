<?php

namespace App\Models\Geonames;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperGeoname
 */
class Geoname extends AbstractModel
{
    /** @var string */
    protected $table = 'corpus_geonames_geonames';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'geonameid';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
