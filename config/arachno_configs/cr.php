<?php

use App\Services\Arachno\Crawlers\Sources\Cr\CostaRicaInformationSystem;
use App\Services\Arachno\Crawlers\Sources\Cr\CostaRicaNationalCSO;

return [
    'cr-pgrweb-go' => CostaRicaInformationSystem::class,
    'cr-cso-go' => CostaRicaNationalCSO::class,
];
