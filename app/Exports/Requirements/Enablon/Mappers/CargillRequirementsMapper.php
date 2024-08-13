<?php

namespace App\Exports\Requirements\Enablon\Mappers;

use App\Contracts\Exports\SpreadsheetMapper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CargillRequirementsMapper implements SpreadsheetMapper
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
            'Regulation' => '//Regulation',
            'Citation' => '//Citation',
            'Code' => 'Code',
            'Title' => 'Translated - Title [EN]',
            'Version' => 'Version',
            'Issued Date' => 'Issued Date',
            'Fed. Enfo.' => 'Fed. Enfo.',
            'Domains' => 'Domains',
            'Delayed Effective Date' => 'Delayed Effective Date',
            'Custom Type' => 'Type',
        ];
    }
}
