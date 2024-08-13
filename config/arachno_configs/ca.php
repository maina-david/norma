<?php

use App\Services\Arachno\Crawlers\Sources\Ca\CanadaBcLaws;
use App\Services\Arachno\Crawlers\Sources\Ca\CanadaGazette;
use App\Services\Arachno\Crawlers\Sources\Ca\CanadaGazetteOld;
use App\Services\Arachno\Crawlers\Sources\Ca\CanadaLawsLois;
use App\Services\Arachno\Crawlers\Sources\Ca\CanadaOntario;
use App\Services\Arachno\Crawlers\Sources\Ca\CanadaToronto;

return [
    'ca-bc-laws' => CanadaBcLaws::class,
    'ca-canada-gazette' => CanadaGazette::class,
    'ca-canada-gazette-old' => CanadaGazetteOld::class,
    'ca-toronto' => CanadaToronto::class,
    'ca-ontario' => CanadaOntario::class,
    'ca-laws-lois' => CanadaLawsLois::class,
];
