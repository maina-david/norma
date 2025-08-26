<?php

namespace App\Http\Controllers\Traits;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveNormasManager;

trait GetsNormaAndOrganisation
{
    /**
     * @return array<mixed>
     */
    protected function getActiveNormaAndOrganisation(): array
    {
        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Norma|null */
        $norma = $manager->getActive();
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();

        return [$norma, $organisation];
    }
}
