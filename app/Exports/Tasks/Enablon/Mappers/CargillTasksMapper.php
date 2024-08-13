<?php

namespace App\Exports\Tasks\Enablon\Mappers;

use App\Contracts\Exports\SpreadsheetMapper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CargillTasksMapper implements SpreadsheetMapper
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
            'Requirement' => '//Requirements',
            'Id' => '//Task #',
            'Name [EN]' => 'Name [EN]',
            'Description' => 'Task Description [EN]',
            'Cargill Domain' => 'Domain',
            'Cargill Settings Based On' => 'Settings Based On',
            'Cargill Recurrence Type' => 'Recurrence Type',
            'Cargill Frequency' => 'Frequency',
            'Cargill Type of Selection' => 'Type of Selection',
            'Cargill Day of the Month' => 'Day of the Month',
            'Cargill Week position' => 'Week position',
            'Cargill Usual Week \ Day' => 'Usual Week \ Day',
            'Cargill Month' => 'Month',
            'Cargill Every' => 'Every',
            'Cargill Duration of the Task' => 'Duration of the Task',
            'Cargill Compliance Task \ Type' => 'Compliance Task \ Type',
            'Cargill Functional Groups' => 'Functional Groups',
            'Cargill Active' => 'Active',
            'Cargill Parent' => 'Parent',
            'Cargill Nature' => 'Nature',
        ];
    }
}
