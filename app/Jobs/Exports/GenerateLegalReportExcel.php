<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\NormaOrganisationExport;
use App\Exports\Requirements\LegalReportExcelExport;

class GenerateLegalReportExcel extends NormaAndOrganisationExport
{
    /**
     * {@inheritDoc}
     */
    protected function getExporter(): NormaOrganisationExport
    {
        return app(LegalReportExcelExport::class);
    }
}
