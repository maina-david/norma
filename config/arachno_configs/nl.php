<?php

use App\Services\Arachno\Crawlers\Sources\Nl\NlOfficielebekendmakingen;
use App\Services\Arachno\Crawlers\Sources\Nl\WettenOverheid;

return [
    'nl-officielebekendmakingen' => NlOfficielebekendmakingen::class,
    'nl-wetten-overheid' => WettenOverheid::class,
];
