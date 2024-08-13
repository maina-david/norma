<?php

namespace App\Exports\Customer\Settings;

use App\Enums\Auth\LifecycleStage;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserExport implements FromCollection, WithStyles, ShouldAutoSize, WithHeadings
{
    /**
     * @param Organisation $organisation
     */
    public function __construct(public Organisation $organisation)
    {
    }

    /**
     * @return array<int, mixed>
     */
    public function headings(): array
    {
        return [
            __('users.title'),
            __('users.first_name'),
            __('users.last_name'),
            __('users.email'),
            __('users.lifecycle_stage'),
            __('users.job_description'),
        ];
    }

    /**
     * Get the collection of users to be exported.
     *
     * @return Collection<array<int, user>>
     */
    public function collection(): Collection
    {
        $stages = LifecycleStage::lang();

        /** @var Collection<array<int, user>> */
        return User::inOrganisation($this->organisation->id)
            ->active()
            ->orderBy('fname')
            ->get(['title', 'fname', 'sname', 'email', 'lifecycle_stage', 'job_description'])
            ->map(fn (User $user) => [
                $user->title,
                $user->fname,
                $user->sname,
                $user->email,
                $stages[$user->lifecycle_stage] ?? '',
                $user->job_description,
            ]);
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
