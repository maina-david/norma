<?php

use App\Services\Arachno\Crawlers\Sources\Ch\SwitzerlandBaselProvincialLaw;
use App\Services\Arachno\Crawlers\Sources\Ch\SwitzerlandFederalLaw;

return [
    'ch-basel-stadt' => SwitzerlandBaselProvincialLaw::class,
    'ch-fedlex' => SwitzerlandFederalLaw::class,
];
