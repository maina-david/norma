<?php

use App\Services\Arachno\Crawlers\Sources\Mx\CaliforniaTrabajoLegislativo;
use App\Services\Arachno\Crawlers\Sources\Mx\MexicoJaliscoState;
use App\Services\Arachno\Crawlers\Sources\Mx\MexicoOfficialGazette;
use App\Services\Arachno\Crawlers\Sources\Mx\MexicoStateGovernment;
use App\Services\Arachno\Crawlers\Sources\Mx\MexicoStateOfPueba;

return [
    'mx-california-trabajo-legs' => CaliforniaTrabajoLegislativo::class,
    'mx-congresoweb-congresojal' => MexicoJaliscoState::class,
    'mx-dof-gob' => MexicoOfficialGazette::class,
    'mx-legislacion-edomex-gob' => MexicoStateGovernment::class,
    'mx-ojp-puebla-gob' => MexicoStateOfPueba::class,
];
