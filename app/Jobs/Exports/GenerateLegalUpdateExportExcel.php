<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Notify\LegalUpdateExcelExport;

class GenerateLegalUpdateExportExcel extends LibryoAndOrganisationExport
{
    /**
     * {@inheritDoc}
     */
    protected function getExporter(): LibryoOrganisationExport
    {
        return app(LegalUpdateExcelExport::class);
    }
}
