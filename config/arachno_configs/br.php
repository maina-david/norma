<?php

use App\Services\Arachno\Crawlers\Sources\Br\BrazilBragancaPaulista;
use App\Services\Arachno\Crawlers\Sources\Br\BrazilFederal;
use App\Services\Arachno\Crawlers\Sources\Br\BrazilFederalPlanalto;
use App\Services\Arachno\Crawlers\Sources\Br\SaoPauloCity;
use App\Services\Arachno\Crawlers\Sources\Br\SaoPauloLegislativeAssembly;
use App\Services\Arachno\Crawlers\Sources\Br\SaoPauloState;

return [
    'br-braganca-paulista' => BrazilBragancaPaulista::class,
    'br-brazil-federal-planalto' => BrazilFederalPlanalto::class,
    'br-brazil-federal' => BrazilFederal::class,
    'br-al-sp' => SaoPauloLegislativeAssembly::class,
    'br-sao-paulo-city' => SaoPauloCity::class,
    'br-sao-paulo-state' => SaoPauloState::class,
];
