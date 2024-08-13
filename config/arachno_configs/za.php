<?php

use App\Services\Arachno\Crawlers\Sources\Za\SabinetGazettes;
use App\Services\Arachno\Crawlers\Sources\Za\SouthAfricaSabinetLegislation;

return [
    'za-sabinet-gazettes' => SabinetGazettes::class,
    'za-sabinet-legislation' => SouthAfricaSabinetLegislation::class,
];
