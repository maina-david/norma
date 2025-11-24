<?php

namespace App\Exports\Assess;

use App\Enums\Assess\AssessActivityType;
use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Enums\Ontology\CategoryType;
use App\Exports\Traits\CleansHtmlForExcel;
use App\Http\Controllers\Assess\My\Traits\ComposesResponsesQuery;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use App\Traits\CleansWorksheetTitle;
use App\Traits\Comments\ExportsComments;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssessResponsesExcelExport
{
    use CleansHtmlForExcel;
    use ComposesResponsesQuery;
    use UsesBookmarksTableFilter;
    use ExportsComments;
    use CleansWorksheetTitle;

    /** @var int */
    protected int $progress = 0;

    /** @var Spreadsheet */
    protected Spreadsheet $excel;

    protected User $user;

    protected Organisation $organisation;

    protected ?Norma $norma;

    /** @var array <string, string> */
    protected array $filters = [];

    protected mixed $progressCallback = null;

    protected int $progressTotal = 1;

    protected int $currentRow = 0;

    protected bool $forNorma = true;

    /**
     * Set up the pages for the PDF.
     *
     * @return void
     */
    protected function setUpPage(): void
    {
        $this->excel = new Spreadsheet();
        $this->progress = 0;

        /** @var string */
        $title = __('assess.assess_responses');
        $this->excel->getProperties()
            ->setCreator(config('app.name'))
            ->setLastModifiedBy(config('app.name'))
            ->setTitle($title)
            ->setSubject($title)
            ->setKeywords($title . ' ' . config('app.name'));
    }

    /**
     * @param User                $user
     * @param Organisation        $organisation
     * @param Norma|null         $norma
     * @param array<string,mixed> $filters
     * @param callable|null       $progressCallback
     *
     * @throws Exception
     *
     * @return Spreadsheet
     */
    public function export(
        User $user,
        Organisation $organisation,
        ?Norma $norma,
        array $filters = [],
        ?callable $progressCallback = null
    ): Spreadsheet {
        $this->user = $user;
        $this->organisation = $organisation;
        $this->norma = $norma;
        $this->filters = $this->updateBookmarkFilters($filters, $user);
        $this->progressCallback = $progressCallback;

        $this->setUpPage();

        $this->forNorma = (bool) $norma;

        $workbook = $norma
            ? $this->buildForNorma()
            : $this->buildForOrganisation();

        $workbook->removeSheetByIndex(0);

        return $workbook;
    }

    /**
     * Build the spreadsheet for the organisation as a whole.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return Spreadsheet
     */
    protected function buildForOrganisation(): Spreadsheet
    {
        $query = Norma::forOrganisation($this->organisation->id)
            ->whereHas('assessmentItemResponses', fn ($query) => $query->filter($this->filters))
            ->userHasAccess($this->user);

        $this->progressTotal = $query->clone()->count('id');

        $query->cursor()->each(function ($norma) {
            $this->norma = $norma;
            $this->buildForNorma();

            if (!is_null($this->progressCallback)) {
                $this->progress++;

                $percentage = round(($this->progress / $this->progressTotal) * 100);

                call_user_func_array($this->progressCallback, [min($percentage, 99)]);
            }
        });

        return $this->excel;
    }

    /**
     * Build the spreadsheet for the norma.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return Spreadsheet
     */
    protected function buildForNorma(): Spreadsheet
    {
        if (!$this->norma) {
            // @codeCoverageIgnoreStart
            return $this->excel;
            // @codeCoverageIgnoreEnd
        }

        $responseId = sprintf('%s.id', (new AssessmentItemResponse())->getTable());

        /** @var Builder $query */
        $query = AssessmentItemResponse::forNorma($this->norma)->filter($this->filters);

        $query = $this->composeResponsesQuery($query, $this->norma, $this->organisation, $this->user)
            ->with([
                'assessmentItem.references' => function ($query) {
                    $query->active()->forActiveWork()->forNorma($this->norma)->select(['id', 'work_id']);
                },
                'assessmentItem.categories' => fn ($q) => $q->where('category_type_id', CategoryType::CONTROL->value),
                ...$this->eagerCommentsForNorma($this->norma),
            ])
            ->addSelect([
                'justification' => AssessmentActivity::where('activity_type', AssessActivityType::answerChange()->value)
                    ->whereColumn('assessment_item_response_id', $responseId)
                    ->orderBy('id', 'desc')
                    ->take(1)
                    ->select(['notes']),
            ]);

        $this->currentRow = 0;
        $sheet = $this->excel->createSheet();

        $title = explode('-', $this->norma->title);

        if (count($title) > 1) {
            array_shift($title);
        }

        $title = trim(implode('-', $title));

        $sheet->setTitle($this->prepareSheetTitle($title));

        if ($this->forNorma) {
            $this->progressTotal = $query->clone()->count();
        }

        $query->chunk(20, function ($responses) use ($sheet) {
            foreach ($responses as $response) {
                /** @var AssessmentItemResponse $response */
                $this->writeRow($response, $sheet);

                if ($this->forNorma && !is_null($this->progressCallback)) {
                    $this->progress++;

                    $percentage = round(($this->progress / $this->progressTotal) * 100);

                    call_user_func_array($this->progressCallback, [min($percentage, 99)]);
                }

                $this->currentRow++;
            }
        });

        $sheet->setCellValue('A1', __('interface.id'));
        $sheet->setCellValue('B1', __('assess.assessment_item.index_title'));
        $sheet->setCellValue('C1', __('assess.legal_reference'));
        $sheet->setCellValue('D1', __('assess.risk_rating'));
        $sheet->setCellValue('E1', __('assess.category'));
        $sheet->setCellValue('F1', __('assess.assessment_item_response.response_justification'));
        $sheet->setCellValue('G1', __('assess.assessment_item_response.control_topics'));
        $sheet->setCellValue('H1', __('assess.assessment_item_response.answer'));
        $sheet->setCellValue('I1', __('assess.metrics.last_answered'));
        $sheet->setCellValue('J1', __('assess.assessment_item_response.answered_by'));
        $sheet->setCellValue('K1', __('comments.comments_added'));
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->getStyle('A1:K1000')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->setSelectedCell('A1');

        return $this->excel;
    }

    /**
     * Writes the next row in the spreadsheet.
     *
     * @param AssessmentItemResponse $response
     * @param Worksheet              $sheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return void
     */
    protected function writeRow(AssessmentItemResponse $response, Worksheet $sheet): void
    {
        $row = $this->currentRow + 2;

        $references = $response->assessmentItem->references
            ->map(fn ($ref) => route('my.corpus.references.show', ['reference' => $ref->id]))
            ->join("\n");

        $sheet->setCellValue('A' . $row, $response->assessment_item_id);
        $sheet->setCellValue('B' . $row, $response->assessmentItem->description ?? '');
        $sheet->setCellValue('C' . $row, $references);
        $sheet->setCellValue('D' . $row, isset($response->assessmentItem->risk_rating) ? RiskRating::fromValue($response->assessmentItem->risk_rating)->label() : '');
        $sheet->setCellValue('E' . $row, $response->assessmentItem->legalDomain->title ?? '');
        /** @phpstan-ignore-next-line  */
        $sheet->setCellValue('F' . $row, $response->justification);
        $sheet->setCellValue('G' . $row, $response->assessmentItem->categories->pluck('display_label')->join(','));
        $sheet->setCellValue('H' . $row, $response->answer ? ResponseStatus::fromValue($response->answer)->label() : '');
        $sheet->setCellValue('I' . $row, $response->answered_at);
        $sheet->setCellValue('J' . $row, $response->lastAnsweredBy->full_name ?? '');

        $this->writeComments($response->comments, $sheet, "A{$row}", "K{$row}");
    }
}
