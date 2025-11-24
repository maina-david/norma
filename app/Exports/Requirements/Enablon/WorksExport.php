<?php

namespace App\Exports\Requirements\Enablon;

use App\Contracts\Exports\NormaOrganisationExport;
use App\Exports\Traits\SetsUpNormaOrgExport;
use App\Exports\Traits\UsesExportMapper;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Customer\Pivots\NormaWork;
use App\Models\Geonames\CountryInfo;
use App\Models\Geonames\Location;
use App\Traits\CleansWorksheetTitle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class WorksExport implements NormaOrganisationExport
{
    use CleansWorksheetTitle;
    use SetsUpNormaOrgExport;
    use UsesExportMapper;

    /** @var array<string, string> */
    protected array $matchedCountries = [];

    /**
     * Get the default mapping of the headers.
     *
     * @return array<int, string>
     */
    protected function defaultColumnOrder(): array
    {
        return [
            'Change Class',
            'Code',
            'Comment',
            'Comments',
            'Content Provider Id',
            'Content Provider Parent Id',
            'Cost',
            'Create Attestation Requirement',
            'Description',
            'Domains',
            'Domains Editable',
            'Effective Date',
            'Effective Date Delayed',
            'Geographies',
            'Id',
            'Issued By',
            'Issued Date',
            'Keywords',
            'Last Modified Date',
            'Library \ Library Documents',
            'Link',
            'Link Law Proposal',
            'Original Copy Location',
            'Original Regulation',
            'Other Copies Location',
            'Permit does not expire',
            'Previous Version',
            'Published Document',
            'RCM Content Provider \ Content Provider',
            'Received by',
            'Red-line Link',
            'Regulations',
            'Relevant Authority',
            'Renewal deadline',
            'Renewal Status',
            'Research Date',
            'Responsible',
            'Status',
            'Summary',
            'Text Category',
            'Text Files',
            'Title',
            'Type',
            'Unit',
            'Valid until',
            'Version',
            'View Document [AR]',
            'View Document [DE]',
            'View Document [EN]',
            'View Document [ES]',
            'View Document [FR]',
            'View Document [ZH]',
        ];
    }

    /**
     * Get the value of the given column.
     *
     * @param string $column
     * @param mixed  $row
     *
     * @return string|null
     */
    protected function getRowValue(string $column, mixed $row): ?string
    {
        return match ($column) {
            'Code' => '13',
            'Description', 'Title' => $row->title,
            'Domains' => '9',
            'Effective Date' => $row->effective_date ? Carbon::parse($row->effective_date)->format('m/d/Y') : '',
            'Geographies' => $this->getAlphaFromFlag($row->flag ?? ''),
            'Id' => $row->id,
            'Issued Date' => $row->work_date ? Carbon::parse($row->work_date)->format('m/d/Y') : '',
            'Text Category' => '103',
            'Type' => '1',
            'Version' => $row->active_work_expression_id,
            default => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    protected function getExportName(): string
    {
        return __('corpus.enablon.types.regulation_export');
    }

    /**
     * {@inheritDoc}
     */
    public function forNorma(
        Norma $norma,
        Organisation $organisation,
        User $user,
        array $filters = [],
        ?callable $progressCallback = null
    ): Spreadsheet {
        $this->setupExport($user, $organisation, $norma, $progressCallback);

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

        $this->getWorksQuery()->chunk(300, function ($works) use ($columns, $sheet) {
            foreach ($works as $work) {
                $this->rowNumber++;

                /** @var Work $work */
                $this->writeRow($sheet, $work, $columns);
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
    protected function getWorksQuery(): Builder
    {
        $places = Norma::where('organisation_id', $this->organisation->id)
            ->select([qualify_column(Norma::class, 'id')]);
        $places = $this->norma ? [$this->norma->id] : $places;

        $query = NormaWork::whereIn('place_id', $places)
            ->join(get_table(Work::class), qualify_column(Work::class, 'id'), 'work_id')
            ->join(get_table(Location::class), qualify_column(Location::class, 'id'), 'primary_location_id')
            ->whereNull(qualify_column(Work::class, 'organisation_id'))
            ->select([
                qualify_column(Work::class, '*'),
                qualify_column(Location::class, 'flag') . ' as flag',
            ])
            ->orderBy(qualify_column(Work::class, 'title'));

        $this->progressTotal = $query->clone()->count();

        /** @var Builder */
        return $query;
    }

    /**
     * Get the alpha value from the flag.
     *
     * @param string $flag
     *
     * @return string
     */
    protected function getAlphaFromFlag(string $flag): string
    {
        $flag = explode('-', $flag)[0];
        $flag = explode('_', $flag)[0];

        if (strlen($flag) == 2) {
            $info = $this->matchedCountries[$flag] ?? CountryInfo::where('iso_alpha2', $flag)->first()?->iso_alpha3 ?? '';
            $this->matchedCountries[$flag] = $info;
            $flag = $info;
        }

        return strtoupper($flag);
    }
}
