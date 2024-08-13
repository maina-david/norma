<?php

use App\Services\Arachno\Crawlers\Sources\Nz\NewZealandFederalGazette;
use App\Services\Arachno\Crawlers\Sources\Nz\NewZealandLegislationGov;

return [
    'nz-new-zealand-federal-gazette' => NewZealandFederalGazette::class,
    'nz-legislation-gov' => NewZealandLegislationGov::class,
];
