<?php

use App\Services\Arachno\Crawlers\Sources\Kr\SouthKoreaElectronicGazette;
use App\Services\Arachno\Crawlers\Sources\Kr\SouthKoreaLaws;

return [
    'kr-gwanbo-go' => SouthKoreaElectronicGazette::class,
    'kr-south-korea-laws' => SouthKoreaLaws::class,
];
