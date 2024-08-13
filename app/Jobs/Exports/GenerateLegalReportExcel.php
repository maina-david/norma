<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Requirements\LegalReportExcelExport;

class GenerateLegalReportExcel extends LibryoAndOrganisationExport
{
    /**
     * {@inheritDoc}
     */
    protected function getExporter(): LibryoOrganisationExport
    {
        return app(LegalReportExcelExport::class);
    }
}
