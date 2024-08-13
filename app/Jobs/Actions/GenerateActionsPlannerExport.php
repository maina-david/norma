<?php

namespace App\Jobs\Actions;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Actions\ActionsPlannerExport;
use App\Jobs\Exports\LibryoAndOrganisationExport;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;

class GenerateActionsPlannerExport extends LibryoAndOrganisationExport
{
    /** @var bool */
    private bool $usingControl;

    public function __construct(
        string $tempFileName,
        User $user,
        ?Libryo $libryo,
        Organisation $organisation,
        array $filters = [],
        bool $usingControl = false,
    ) {
        parent::__construct($tempFileName, $user, $libryo, $organisation, $filters);
        $this->usingControl = $usingControl;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExporter(): LibryoOrganisationExport
    {
        $export = app(ActionsPlannerExport::class);

        return $this->usingControl ? $export->usingControl() : $export;
    }
}
