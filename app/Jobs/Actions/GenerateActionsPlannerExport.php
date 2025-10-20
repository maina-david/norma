<?php

namespace App\Jobs\Actions;

use App\Contracts\Exports\NormaOrganisationExport;
use App\Exports\Actions\ActionsPlannerExport;
use App\Jobs\Exports\NormaAndOrganisationExport;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;

class GenerateActionsPlannerExport extends NormaAndOrganisationExport
{
    /** @var bool */
    private bool $usingControl;

    public function __construct(
        string $tempFileName,
        User $user,
        ?Norma $norma,
        Organisation $organisation,
        array $filters = [],
        bool $usingControl = false,
    ) {
        parent::__construct($tempFileName, $user, $norma, $organisation, $filters);
        $this->usingControl = $usingControl;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExporter(): NormaOrganisationExport
    {
        $export = app(ActionsPlannerExport::class);

        return $this->usingControl ? $export->usingControl() : $export;
    }
}
