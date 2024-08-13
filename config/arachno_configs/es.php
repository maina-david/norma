<?php

use App\Services\Arachno\Crawlers\Sources\Es\CityOfMadrid;
use App\Services\Arachno\Crawlers\Sources\Es\CommunityOfMadrid;
use App\Services\Arachno\Crawlers\Sources\Es\SpainAndaluciaParliament;
use App\Services\Arachno\Crawlers\Sources\Es\SpainBoletinOficial;
use App\Services\Arachno\Crawlers\Sources\Es\SpainGrenadaCityHall;

return [
    'es-boe' => SpainBoletinOficial::class,
    'es-community-of-madrid' => CommunityOfMadrid::class,
    'es-city-of-madrid' => CityOfMadrid::class,
    'es-granada' => SpainGrenadaCityHall::class,
    'es-parlamentodeandalucia' => SpainAndaluciaParliament::class,
];
