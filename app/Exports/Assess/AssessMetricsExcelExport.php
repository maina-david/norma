<?php

namespace App\Exports\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Exports\Traits\CleansHtmlForExcel;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Assess\AssessStatsService;
use App\Services\Assess\QuarterlyReportsManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AssessMetricsExcelExport
{
    use CleansHtmlForExcel;

    /** @var int */
    protected int $progress = 0;

    /** @var Spreadsheet */
    protected $excel;

    /** @var QuarterlyReportsManager */
    protected QuarterlyReportsManager $quarterlyManager;

    /** @var AssessStatsService */
    protected AssessStatsService $statsService;

    /**
     * @param Organisation         $organisation
     * @param Libryo|null          $libryo
     * @param User                 $user
     * @param array<string, mixed> $filters
     * @param callable|null|null   $progressCallback
     *
     * @return Spreadsheet
     */
    public function forOrganisation(Organisation $organisation, ?Libryo $libryo, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        /** @var Builder */
        $query = Libryo::forOrganisation($organisation->id)->active()->userHasAccess($user);

        if ($libryo) {
            // @codeCoverageIgnoreStart
            $query->whereKey($libryo);
            // @codeCoverageIgnoreEnd
        }

        $query->forAssessMetrics()->orderBy('risk_rating', 'DESC');

        if (count($filters) > 0) {
            $query->filter($filters);
        }

        $query->orderBy((new Libryo())->qualifyColumn('id'))->orderBy('title');

        $this->quarterlyManager = app(QuarterlyReportsManager::class);
        $this->statsService = app(AssessStatsService::class);

        return $this->build($query, $filters, $progressCallback);
    }

    /**
     * @param Builder              $query
     * @param array<string, mixed> $filters
     * @param callable|null|null   $progressCallback
     *
     * @return Spreadsheet
     */
    public function build(
        Builder $query,
        array $filters = [],
        ?callable $progressCallback = null,
    ): Spreadsheet {
        $this->setUpPage();
        $this->setUpQuarters();
        $this->progress = 0;
        $progressTotal = $query->count();

        $query->chunk(50, function ($libryoMetrics) use ($filters, $progressCallback, $progressTotal) {
            foreach ($libryoMetrics as $row => $libryo) {
                /** @var Libryo $libryo */
                $this->writeRow($libryo, $this->progress);
                $this->writeQuarterRow($libryo, $filters, $this->progress);
                $this->progress++;
                $percentage = round(($this->progress / $progressTotal) * 100);
                $percentage = $percentage > 99 ? 99 : $percentage;
                if (!is_null($progressCallback)) {
                    call_user_func_array($progressCallback, [$percentage]);
                }
            }
        });

        foreach ($this->excel->getAllSheets() as $ws) {
            $ws->setCellValue('A1', __('assess.risk_rating'));
            $ws->setCellValue('B1', __('customer.libryo.libryo_stream'));
            $ws->setCellValue('C1', __('assess.metrics.last_answered'));
            $ws->setCellValue('D1', __('assess.metrics.total_' . ResponseStatus::yes()->value));
            $ws->setCellValue('E1', __('assess.metrics.total_' . ResponseStatus::no()->value));
            $ws->setCellValue('F1', __('assess.metrics.total_' . ResponseStatus::notApplicable()->value));
            $ws->setCellValue('G1', __('assess.metrics.total_' . ResponseStatus::notAssessed()->value));
            $ws->setCellValue('H1', __('assess.risk_rating_descriptions.labels.non_compliant_items_by_rating_' . RiskRating::high()->value));
            $ws->setCellValue('I1', __('assess.risk_rating_descriptions.labels.non_compliant_items'));
            $ws->getStyle('A1:J1')->getFont()->setBold(true);

            $ws->getStyle('A1:I500')
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP)
                ->setWrapText(true);
            $ws->getDefaultColumnDimension()->setWidth(16);
            $ws->getColumnDimension('A')->setWidth(40);
            $ws->getColumnDimension('B')->setWidth(80);
            $ws->getColumnDimension('C')->setWidth(40);
            $ws->setSelectedCell('A1');
        }

        $this->excel->setActiveSheetIndex(0);

        return $this->excel;
    }

    /**
     * Set up the pages for the PDF.
     *
     * @return void
     */
    protected function setUpPage(): void
    {
        $this->excel = new Spreadsheet();

        /** @var string */
        $title = __('assess.assess_metrics');
        $this->excel->getProperties()
            ->setCreator(config('app.name'))
            ->setLastModifiedBy(config('app.name'))
            ->setTitle($title)
            ->setSubject($title)
            ->setKeywords($title . ' ' . config('app.name'));
    }

    /**
     * Set up the quarter sheets.
     *
     * @throws Exception
     *
     * @return void
     */
    public function setUpQuarters(): void
    {
        $startOfQuarter = Carbon::now()->startOfMonth()->subQuarter()->startOfQuarter();
        $endOfQuarter = Carbon::now()->startOfMonth()->subQuarter()->endOfQuarter();

        $this->excel->getSheet(0)->setTitle('Current');

        $this->excel->createSheet(1)->setTitle($startOfQuarter->format('M, Y') . ' - ' . $endOfQuarter->format('M, Y'));

        $startOfQuarter = $startOfQuarter->startOfMonth()->subQuarter()->startOfQuarter();
        $endOfQuarter = $endOfQuarter->startOfMonth()->subQuarter()->endOfQuarter();

        $this->excel->createSheet(2)->setTitle($startOfQuarter->format('M, Y') . ' - ' . $endOfQuarter->format('M, Y'));

        $startOfQuarter = $startOfQuarter->startOfMonth()->subQuarter()->startOfQuarter();
        $endOfQuarter = $endOfQuarter->startOfMonth()->subQuarter()->endOfQuarter();

        $this->excel->createSheet(3)->setTitle($startOfQuarter->format('M, Y') . ' - ' . $endOfQuarter->format('M, Y'));

        $this->excel->setActiveSheetIndex(0);
    }

    /**
     * Writes the next row in the spreadsheet.
     *
     * @param Libryo $libryo
     * @param int    $row
     *
     * @return void
     */
    protected function writeRow(Libryo $libryo, int $row): void
    {
        $row = $row + 2; // Row to insert content at.
        $ws = $this->excel->getActiveSheet();

        $ws->setCellValue('A' . $row, RiskRating::lang()[$libryo['risk_rating']] ?? '');
        $ws->setCellValue('B' . $row, $libryo->title ?? '');
        $ws->setCellValue('C' . $row, $libryo['last_activity_date'] ?? '');
        $ws->setCellValue('D' . $row, $libryo['count_by_status_' . ResponseStatus::yes()->value]);
        $ws->setCellValue('E' . $row, $libryo['count_by_status_' . ResponseStatus::no()->value]);
        $ws->setCellValue('F' . $row, $libryo['count_by_status_' . ResponseStatus::notApplicable()->value]);
        $ws->setCellValue('G' . $row, $libryo['count_by_status_' . ResponseStatus::notAssessed()->value]);
        $ws->setCellValue('H' . $row, $libryo['total_non_compliant_items_' . RiskRating::high()->value]);
        $ws->setCellValue('I' . $row, ($libryo['percentage_non_compliant_items'] ?? '') . '%');
    }

    /**
     * @param Libryo               $libryo
     * @param array<string, mixed> $filters
     * @param int                  $row
     *
     * @throws Exception
     *
     * @return void
     */
    protected function writeQuarterRow(Libryo $libryo, array $filters, int $row): void
    {
        /** @var Builder $resQuery */
        $resQuery = AssessmentItemResponse::forLibryo($libryo);

        if (!empty($filters)) {
            $resQuery->filter($filters);
        }

        /** @var Builder $query */
        $query = Libryo::whereKey($libryo->id);

        $quarterly = array_values([
            ...$this->quarterlyManager->getResponseData($query, $resQuery)['raw'],
        ]);

        foreach ($quarterly as $index => $quarter) {
            $this->excel->setActiveSheetIndex($index + 1);

            $libryo->setAttribute('risk_rating', $quarter['risk_rating']->value);

            $libryo->setAttribute('count_by_status_' . ResponseStatus::yes()->value, $quarter[ResponseStatus::yes()->value]);
            $libryo->setAttribute('count_by_status_' . ResponseStatus::no()->value, $quarter[ResponseStatus::no()->value]);
            $libryo->setAttribute('count_by_status_' . ResponseStatus::notApplicable()->value, $quarter[ResponseStatus::notApplicable()->value]);
            $libryo->setAttribute('count_by_status_' . ResponseStatus::notAssessed()->value, $quarter[ResponseStatus::notAssessed()->value]);
            $libryo->setAttribute('total_non_compliant_items_' . RiskRating::high()->value, $quarter['total_non_compliant_items_' . RiskRating::high()->value]);
            $libryo->setAttribute('percentage_non_compliant_items', $quarter[ResponseStatus::no()->value] !== 0 ? round(($quarter[ResponseStatus::no()->value] / $quarter['total']) * 100) : 0);

            $this->writeRow($libryo, $row);
        }

        $this->excel->setActiveSheetIndex(0);
    }
}
