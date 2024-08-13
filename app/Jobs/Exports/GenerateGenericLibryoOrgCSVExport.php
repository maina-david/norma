<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class GenerateGenericLibryoOrgCSVExport extends LibryoAndOrganisationExport
{
    /**
     * @param string                            $tempFileName
     * @param \App\Models\Auth\User             $user
     * @param \App\Models\Customer\Libryo|null  $libryo
     * @param \App\Models\Customer\Organisation $organisation
     * @param string                            $exporterClass
     * @param array<string, mixed>              $filters
     * @param string|null                       $spreadsheetMapper
     */
    public function __construct(
        string $tempFileName,
        User $user,
        ?Libryo $libryo,
        Organisation $organisation,
        private string $exporterClass,
        array $filters = [],
        protected ?string $spreadsheetMapper = null
    ) {
        parent::__construct($tempFileName, $user, $libryo, $organisation, $filters, $spreadsheetMapper);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExporter(): LibryoOrganisationExport
    {
        return app($this->exporterClass);
    }

    /**
     * {@inheritDoc}
     */
    protected function getWriter(Spreadsheet $spreadsheet): BaseWriter
    {
        return new Csv($spreadsheet);
    }
}
