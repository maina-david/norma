<?php

use App\Services\Arachno\Crawlers\Sources\Jp\JapanElaws;
use App\Services\Arachno\Crawlers\Sources\Jp\ReikiMetroTokyo;

return [
    'jp-elaws-gov' => JapanElaws::class,
    'jp-reiki-metro-tokyo' => ReikiMetroTokyo::class,
];
