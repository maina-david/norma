<?php

use App\Services\Arachno\Crawlers\Sources\Cn\ChinaNational;
use App\Services\Arachno\Crawlers\Sources\Cn\ShanghaiCity;

return [
    'cn-shanghai-city' => ShanghaiCity::class,
    'cn-china-national' => ChinaNational::class,
];
