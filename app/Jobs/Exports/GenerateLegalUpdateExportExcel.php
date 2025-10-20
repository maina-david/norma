<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\NormaOrganisationExport;
use App\Exports\Notify\LegalUpdateExcelExport;

class GenerateLegalUpdateExportExcel extends NormaAndOrganisationExport
{
    /**
     * {@inheritDoc}
     */
    protected function getExporter(): NormaOrganisationExport
    {
        return app(LegalUpdateExcelExport::class);
    }
}
