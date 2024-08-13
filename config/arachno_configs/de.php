<?php

use App\Services\Arachno\Crawlers\Sources\De\BavariaProvince;
use App\Services\Arachno\Crawlers\Sources\De\BavariaProvinceVerkuendung;
use App\Services\Arachno\Crawlers\Sources\De\GermanyBundesgesetzblatt;
use App\Services\Arachno\Crawlers\Sources\De\GermanyDguvRegs;
use App\Services\Arachno\Crawlers\Sources\De\GesetzeImInternet;
use App\Services\Arachno\Crawlers\Sources\De\KoblenzVerbindet;

return [
    'de-bundesgesetzblatt' => GermanyBundesgesetzblatt::class,
    'de-germany-dguv-regs' => GermanyDguvRegs::class,
    'de-gesetze-bayern' => BavariaProvince::class,
    'de-gesetze-im-internet' => GesetzeImInternet::class,
    'de-koblenz-verbindet' => KoblenzVerbindet::class,
    'de-verkuendung-bayern' => BavariaProvinceVerkuendung::class,
];
