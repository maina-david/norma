<?php

use App\Services\Arachno\Crawlers\Sources\Au\AuNewSouthWales;
use App\Services\Arachno\Crawlers\Sources\Au\AuQueensland;
use App\Services\Arachno\Crawlers\Sources\Au\AustraliaFederalRegister;
use App\Services\Arachno\Crawlers\Sources\Au\AuVictoriaState;

return [
    //    'au-legislation-gazette' => AustraliaFederalRegister::class,
    'au-queensland' => AuQueensland::class,
    'au-legislation' => AustraliaFederalRegister::class,
    'au-victoria-legislation' => AuVictoriaState::class,
    'au-nsw' => AuNewSouthWales::class,
];
