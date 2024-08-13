<?php

namespace App\Exports\Traits;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait UsesExportMapper
{
    /** @var string|null */
    protected ?string $mapper = null;

    /**
     * Get the default mapping of the headers.
     *
     * @return array<int, string>
     */
    abstract protected function defaultColumnOrder(): array;

    /**
     * Get the value of the given column.
     *
     * @param string $column
     * @param mixed  $row
     *
     * @return string|null
     */
    abstract protected function getRowValue(string $column, mixed $row): ?string;

    /**
     * Set mapper to be used to update the columns.
     *
     * @param string|null $mapper
     *
     * @return self
     */
    public function setMapper(?string $mapper): self
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * Generate the sheet header.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array<string, string>                         $columns
     *
     * @return void
     */
    protected function writeColumnHeaders(Worksheet $sheet, array $columns): void
    {
        $insertTo = 'A';
        $values = array_values($columns);

        foreach ($values as $column) {
            $sheet->setCellValue("{$insertTo}1", $column);
            $insertTo = str_increment($insertTo);
        }
    }

    /**
     * Generate the sheet header.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param mixed                                         $row
     * @param array<string, string>                         $columns
     *
     * @return void
     */
    protected function writeRow(Worksheet $sheet, mixed $row, array $columns): void
    {
        $insertTo = 'A';
        $keys = array_keys($columns);

        foreach ($keys as $column) {
            $sheet->setCellValue("{$insertTo}{$this->rowNumber}", $this->getRowValue($column, $row));
            $insertTo = str_increment($insertTo);
        }
    }

    /**
     * Get the correct sheet columns.
     *
     * @return array<string, string>
     */
    protected function getSheetColumns(): array
    {
        $defaultColumns = $this->defaultColumnOrder();
        $mappedColumns = $this->mapper ? app($this->mapper)->map() : null;

        return $mappedColumns ?? collect($defaultColumns)->mapWithKeys(fn ($val) => [$val => $val])->all();
    }
}
