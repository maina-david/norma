<?php

namespace App\Exports\Customer\Settings;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserImportTemplateExport implements FromCollection, WithStyles, ShouldAutoSize, WithHeadings
{
    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return [
            __('users.first_name'),
            __('users.last_name'),
            __('users.email'),
            __('users.mobile_country_code'),
            __('users.mobile_phone'),
        ];
    }

    /**
     * Get the default collection to be added to the export.
     *
     * @return Collection<array<int, string>>
     */
    public function collection(): Collection
    {
        return collect([]);
    }

    /**
     * Style the first row to be bold.
     *
     * @codeCoverageIgnore
     *
     * @param Worksheet $sheet
     *
     * @return bool[][][]
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
