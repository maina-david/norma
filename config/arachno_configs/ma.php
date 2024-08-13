<?php

use App\Services\Arachno\Crawlers\Sources\Ma\MoroccoArabeAccueilNational;
use App\Services\Arachno\Crawlers\Sources\Ma\MoroccoMinistryofEnergyTransition;
use App\Services\Arachno\Crawlers\Sources\Ma\MoroccoNational;

return [
    'ma-arabe-accueil-national' => MoroccoArabeAccueilNational::class,
    'ma-environnement-go' => MoroccoMinistryofEnergyTransition::class,
    'ma-morocco-national' => MoroccoNational::class,
];
