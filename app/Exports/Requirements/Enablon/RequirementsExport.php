<?php

namespace App\Exports\Requirements\Enablon;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Traits\SetsUpLibryoOrgExport;
use App\Exports\Traits\UsesExportMapper;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\LibryoReference;
use App\Traits\CleansWorksheetTitle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class RequirementsExport implements LibryoOrganisationExport
{
    use CleansWorksheetTitle;
    use SetsUpLibryoOrgExport;
    use UsesExportMapper;

    /**
     * {@inheritDoc}
     */
    protected function getExportName(): string
    {
        return __('corpus.enablon.types.requirements_export');
    }

    /**
     * Get the default mapping of the headers.
     *
     * @return array<int, string>
     */
    protected function defaultColumnOrder(): array
    {
        return [
            'Action Required',
            'Applicability Type  \\ ',
            'Change Class',
            'Change Note',
            'Citation',
            'Code',
            'Content Provider',
            'Content Provider Id',
            'Delayed Effective Date',
            'Domains',
            'Effective Date',
            'Fed. Enfo.',
            'Formula',
            'Id',
            'Issued Date',
            'Link',
            'Link \\ Text Status',
            'Original Citation',
            'Parent Content Provider Id',
            'Previous Version',
            'Red-line Link',
            'Regulation',
            'Status',
            'Text Files',
            'Title',
            'Type',
            'Valid Until',
            'Version',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowValue(string $column, mixed $row): ?string
    {
        return match ($column) {
            'Citation', 'Code', 'Id', 'Original Citation' => $row->id,
            'Domains' => '9',
            'Effective Date' => $row->effective_date ? Carbon::parse($row->effective_date)->format('m/d/Y') : '',
            'Issued Date' => $row->work_date ? Carbon::parse($row->work_date)->format('m/d/Y') : '',
            'Regulation' => $row->work_id,
            'Title' => $row->title,
            'Version' => $row->active_work_expression_id,
            default => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public function forLibryo(
        Libryo $libryo,
        Organisation $organisation,
        User $user,
        array $filters = [],
        ?callable $progressCallback = null
    ): Spreadsheet {
        $this->setupExport($user, $organisation, $libryo, $progressCallback);

        return $this->build();
    }

    /**
     * {@inheritDoc}
     */
    public function forOrganisation(
        Organisation $organisation,
        User $user,
        array $filters = [],
        ?callable $progressCallback = null
    ): Spreadsheet {
        $this->setupExport($user, $organisation, null, $progressCallback);

        return $this->build();
    }

    /**
     * Build the spreadsheet.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function build(): Spreadsheet
    {
        $sheet = $this->addNewSheet(false);
        $columns = $this->getSheetColumns();
        $this->writeColumnHeaders($sheet, $columns);

        $this->getRequirementsQuery()->chunk(500, function ($requirements) use ($columns, $sheet) {
            foreach ($requirements as $reference) {
                $this->rowNumber++;

                /** @var \App\Models\Corpus\Reference $reference */
                $this->writeRow($sheet, $reference, $columns);
                $this->incrementProgress();
            }
        });

        $this->excel->removeSheetByIndex(0);
        $this->excel->setActiveSheetIndex(0);

        return $this->excel;
    }

    /**
     * Get the works query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getRequirementsQuery(): Builder
    {
        $places = Libryo::where('organisation_id', $this->organisation->id)
            ->select([qualify_column(Libryo::class, 'id')]);
        $places = $this->libryo ? [$this->libryo->id] : $places;

        $query = LibryoReference::whereIn('place_id', $places)
            ->join(get_table(Reference::class), qualify_column(Reference::class, 'id'), 'reference_id')
            ->join(get_table(ReferenceText::class), qualify_column(ReferenceText::class, 'reference_id'), qualify_column(Reference::class, 'id'))
            ->join(get_table(Work::class), qualify_column(Work::class, 'id'), 'work_id')
            ->whereNull(qualify_column(Work::class, 'organisation_id'))
            ->select([
                qualify_column(Work::class, 'work_date'),
                qualify_column(Work::class, 'effective_date'),
                qualify_column(Work::class, 'active_work_expression_id'),
                qualify_column(Reference::class, 'id'),
                qualify_column(Reference::class, 'work_id'),
                sprintf('%s as title', (new ReferenceText())->qualifyColumn('plain_text')),
            ])
            ->orderBy(qualify_column(Reference::class, 'work_id'));

        $this->progressTotal = $query->clone()->count();

        /** @var Builder */
        return $query;
    }
}
