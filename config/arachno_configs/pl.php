<?php

use App\Services\Arachno\Crawlers\Sources\Pl\PolandIsap;
use App\Services\Arachno\Crawlers\Sources\Pl\PolandJournalMazowieckiego;
use App\Services\Arachno\Crawlers\Sources\Pl\PolandMazovianVoivodship;
use App\Services\Arachno\Crawlers\Sources\Pl\PolandMonitorPolski;
use App\Services\Arachno\Crawlers\Sources\Pl\PolandNationalLaw;

return [
    'pl-edziennik-mazowieckie' => PolandJournalMazowieckiego::class,
    'pl-monitorpolski-gov' => PolandMonitorPolski::class,
    'pl-poland-isap' => PolandIsap::class,
    'pl-poland-national-law' => PolandNationalLaw::class,
    'pl-uw-mazowieck' => PolandMazovianVoivodship::class,
];
