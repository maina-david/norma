<?php

namespace App\Exports\Requirements\Enablon\Mappers;

use App\Contracts\Exports\SpreadsheetMapper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CargillRequirementContentMapper implements SpreadsheetMapper
{
    /**
     * {@inheritDoc}
     */
    public function addFileExtension(string $filename): string
    {
        return "{$filename}.xlsx";
    }

    /**
     * {@inheritDoc}
     */
    public function writer(Spreadsheet $excel): BaseWriter
    {
        return new Xlsx($excel);
    }

    /**
     * {@inheritDoc}
     */
    public function map(): array
    {
        return [
            '//Citation' => '//Citation',
            '//Requirement' => '//Requirement',
            'Title' => 'Title [EN]',
            'Version' => 'Version',
            'Revision Date' => 'Revision Date',
            'Domains' => 'Domains',
            'Categories' => 'Categories',
            'MultiLink \ Citations/Conditions/Policy Sections' => 'MultiLink \ Citations/Conditions/Policy Sections',
            '//Force Workflow' => '//Force Workflow',
        ];
    }
}
