<?php

use App\Services\Arachno\Crawlers\Sources\Tr\LegislationInformationSystem;
use App\Services\Arachno\Crawlers\Sources\Tr\TurkeyOfficialGazette;

return [
    'tr-mevzuat' => LegislationInformationSystem::class,
    'tr-resmigazete' => TurkeyOfficialGazette::class,
];
