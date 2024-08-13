<?php

namespace App\Http\Controllers\Traits;

use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;

trait GetsLibryoAndOrganisation
{
    /**
     * @return array<mixed>
     */
    protected function getActiveLibryoAndOrganisation(): array
    {
        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var Libryo|null */
        $libryo = $manager->getActive();
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        return [$libryo, $organisation];
    }
}
