<?php

namespace App\Models\Geonames;

use App\Models\AbstractModel;

/**
 * @mixin IdeHelperCountryInfo
 */
class CountryInfo extends AbstractModel
{
    protected $table = 'corpus_geonames_country_info';

    public $incrementing = false;

    protected $primaryKey = 'iso_alpha2';

    protected $keyType = 'string';

    public $timestamps = false;
}
