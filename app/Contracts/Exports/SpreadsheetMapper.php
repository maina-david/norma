<?php

namespace App\Contracts\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;

interface SpreadsheetMapper
{
    /**
     * Add the file extension to the filename.
     *
     * @param string $filename
     *
     * @return string
     */
    public function addFileExtension(string $filename): string;

    /**
     * Get the writer to be used or use the default one.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $excel
     *
     * @return \PhpOffice\PhpSpreadsheet\Writer\BaseWriter
     */
    public function writer(Spreadsheet $excel): BaseWriter;

    /**
     * Update the spreadsheet columns. The key is the default column, the value is the new column.
     *
     * @return array<string, string>
     */
    public function map(): array;
}
