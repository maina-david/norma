<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\NormaOrganisationExport;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class GenerateGenericNormaOrgCSVExport extends NormaAndOrganisationExport
{
    /**
     * @param string                            $tempFileName
     * @param \App\Models\Auth\User             $user
     * @param \App\Models\Customer\Norma|null  $norma
     * @param \App\Models\Customer\Organisation $organisation
     * @param string                            $exporterClass
     * @param array<string, mixed>              $filters
     * @param string|null                       $spreadsheetMapper
     */
    public function __construct(
        string $tempFileName,
        User $user,
        ?Norma $norma,
        Organisation $organisation,
        private string $exporterClass,
        array $filters = [],
        protected ?string $spreadsheetMapper = null
    ) {
        parent::__construct($tempFileName, $user, $norma, $organisation, $filters, $spreadsheetMapper);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExporter(): NormaOrganisationExport
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
