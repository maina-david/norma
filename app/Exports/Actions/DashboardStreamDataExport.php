<?php

namespace App\Exports\Actions;

use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Actions\DashboardStreamDataBuilder;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardStreamDataExport
{
    /** @var int */
    protected int $progress = 0;

    /** @var Spreadsheet */
    protected $excel;

    public function __construct(protected DashboardStreamDataBuilder $dataBuilder)
    {
    }

    /**
     * @param Organisation         $organisation
     * @param array<string>        $columns
     * @param array<string, mixed> $filters
     * @param callable|null|null   $progressCallback
     *
     * @return Spreadsheet
     */
    public function forOrganisation(Organisation $organisation, array $columns, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        $query = $this->dataBuilder->buildQuery($organisation->id, $columns, $filters);

        return $this->build($query, $columns, $progressCallback);
    }

    /**
     * @return void
     */
    protected function setUpSpreadsheet()
    {
        $this->excel = new Spreadsheet();

        /** @var string */
        $title = __('actions.dashboard.actions_dashboard');
        $this->excel->getProperties()
            ->setCreator(config('app.name'))
            ->setLastModifiedBy(config('app.name'))
            ->setTitle($title)
            ->setSubject($title)
            ->setKeywords($title . ' ' . config('app.name'));
    }

    /**
     * @param Builder       $query
     * @param array<string> $columns
     * @param callable|null $progressCallback
     *
     * @return Spreadsheet
     */
    protected function build(
        Builder $query,
        array $columns,
        ?callable $progressCallback = null,
    ): Spreadsheet {
        $this->setUpSpreadsheet();

        $this->buildReport($query, $columns, $progressCallback);

        $this->excel->removeSheetByIndex(0);
        $this->excel->setActiveSheetIndex(0);

        return $this->excel;
    }

    /**
     * @param Builder       $query
     * @param array<string> $columns
     * @param callable|null $progressCallback
     *
     * @return void
     */
    protected function buildReport(Builder $query, array $columns, ?callable $progressCallback = null)
    {
        $index = $this->excel->getSheetCount();
        $ws = new Worksheet($this->excel);
        $this->excel->addSheet($ws);
        $this->excel->setActiveSheetIndex($index);

        foreach ($columns as $index => $column) {
            // this limits it to a maximum of 26 columns, but I don't think we'll have more than that..(for now
            $columnLetter = chr($index + 65);
            $ws->setCellValue($columnLetter . '1', __('actions.dashboard.columns.' . $column));
        }

        $this->progress = 0;
        $progressTotal = $query->count();

        $query->chunk(100, function ($normas) use ($progressCallback, $progressTotal, $columns) {
            foreach ($normas as $row => $norma) {
                /** @var Norma $norma */
                $this->writeNormaItem($norma, $this->progress, $columns);
                $this->progress++;
                $percentage = round(($this->progress / $progressTotal) * 100);
                $percentage = $percentage > 99 ? 99 : $percentage;
                if (!is_null($progressCallback)) {
                    call_user_func_array($progressCallback, [$percentage]);
                }
            }
        });

        $ws->getStyle('A1:M3000')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $ws->getDefaultColumnDimension()->setWidth(20);
        $ws->getStyle('A1:Z1')->getFont()->setBold(true);
        $ws->getColumnDimension('A')->setWidth(30);
        $ws->getColumnDimension('C')->setWidth(50);
    }

    /**
     * @param Norma        $norma
     * @param int           $key
     * @param array<string> $columns
     *
     * @return void
     */
    protected function writeNormaItem(Norma $norma, int $key, array $columns): void
    {
        $row = $key + 2; // Row to insert content at.
        $ws = $this->excel->getActiveSheet();

        foreach ($columns as $index => $column) {
            $columnLetter = chr($index + 65);
            $ws->setCellValue($columnLetter . $row, $norma->{$column});
        }
    }
}
