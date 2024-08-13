<?php

namespace App\Exports\Requirements\Enablon\Mappers;

use App\Contracts\Exports\SpreadsheetMapper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CargillWorksMapper implements SpreadsheetMapper
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
            'Id' => '//Regulation',
            'Code' => 'Code',
            'Title' => 'Translated - Title [EN]',
            'Description' => 'Translated \ Description [EN]',
            'Version' => 'Version',
            'Geographies' => 'Geography',
            'Users' => 'Users',
            'Issued Date' => 'Issued Date',
            'Effective Date Delayed' => 'Effective Date Delayed',
            'Effective Date' => 'Effective Date',
            'Category' => 'Category',
            'Domains' => 'Domains',
            'Custom Type' => 'Type',
            'Status' => 'Status',
        ];
    }
}
