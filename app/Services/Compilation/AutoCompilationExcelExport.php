<?php

namespace App\Services\Compilation;

use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\ContextQuestionDescription;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AutoCompilationExcelExport
{
    public const LIBRYO_COLS_START_COL = 'D';
    public const LIBRYO_COLS_START_NUM = 4;

    /** @var Spreadsheet */
    protected $spreadsheet;

    /** @var Worksheet */
    protected $sheet;

    /** @var Collection<ContextQuestion> */
    protected $questions;

    /** @var array<int, int> */
    protected $questionRowMap = [];

    /** @var array<int|string, array<int>> */
    protected $questionPlaceMap = [];

    /** @var array<int> */
    protected $countryLocations = [];

    /** @var int */
    protected $progress;

    /** @var Organisation */
    protected $organisation;

    /** @var Libryo|null */
    protected $libryo = null;

    /** @var callable|null */
    protected $progressCallback;

    public function __construct(protected LibryoCompilationCacheService $libryoCompilationCacheService)
    {
    }

    /**
     * Import the items in the uploaded excel file. Returns local file path for temporary stored file.
     *
     * @param Organisation                     $organisation
     * @param \App\Models\Customer\Libryo|null $libryo
     * @param callable|null                    $progressCallback
     *
     * @return Spreadsheet
     */
    public function build(Organisation $organisation, ?Libryo $libryo = null, ?callable $progressCallback = null): Spreadsheet
    {
        $this->progress = 0;

        $this->organisation = $organisation;
        $this->libryo = $libryo;
        $this->progressCallback = $progressCallback;

        $this->execute();

        return $this->spreadsheet;
    }

    /**
     * Execute the importation process.
     */
    private function execute(): void
    {
        $this->setupSpreadsheet();
        $this->setupHeadings();
        $this->gatherQuestions();
        $this->addQuestions();
        $this->formatting();
        $this->populateAnswers();
        $this->setProtectedCells();
    }

    /**
     * Setup the excel writer.
     */
    private function setupSpreadsheet(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()->setCreator('Libryo Ltd.');
        $this->spreadsheet->getProperties()->setTitle('Applicability Questions for ' . $this->organisation->title);
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    public function getLibryosQuery(): Builder
    {
        /** @var Builder */
        return Libryo::autocompiled()
            ->active()
            ->when($this->libryo, fn ($query) => $query->where('id', $this->libryo->id ?? null))
            ->forOrganisation($this->organisation->id);
    }

    private function setupHeadings(): void
    {
        $this->sheet->setCellValue('A1', __('compilation.context_brief.id'));
        $this->sheet->setCellValue('B1', __('compilation.context_brief.question'));
        $this->sheet->setCellValue('C1', __('compilation.context_brief.description'));
        $count = $this->getLibryosQuery()->count();
        if ($count > 1000) {
            // @codeCoverageIgnoreStart
            throw new Exception('This organisation has too many libryos to add to one spreadsheet');
            // @codeCoverageIgnoreEnd
        }

        $libryos = $this->getLibryosQuery()
            ->with(['location'])
            ->cursor();

        foreach ($libryos as $index => $libryo) {
            /** @var Libryo $libryo */
            $country = $libryo->location->location_country_id ?? false;
            if ($country) {
                $this->countryLocations[] = $country;
            }
            $col = Coordinate::stringFromColumnIndex(((int) $index) + static::LIBRYO_COLS_START_NUM);
            $this->sheet->setCellValue($col . '1', $libryo->id . ':' . $libryo->title);
        }

        $this->countryLocations = array_unique($this->countryLocations);
    }

    private function gatherQuestions(): void
    {
        /** @var Collection<ContextQuestion> */
        $qs = (new ContextQuestion())->newCollection();
        $this->questions = $qs;
        /** @var Collection<Libryo> */
        $libryos = $this->getLibryosQuery()->cursor();

        foreach ($libryos as $libryo) {
            // we want to make sure the libryo is populated with all applicable questions
            // this only runs at night, so something could have changed since then. e.g. new libryo added to org
            $this->libryoCompilationCacheService->handleLibryoRecompilation($libryo);

            /** @var Collection<ContextQuestion> */
            $questions = ContextQuestion::forLibryo($libryo)
                ->with('descriptions')
                ->get();
            $this->questionPlaceMap[$libryo->id] = $questions->pluck('id')->toArray();
            /** @var Collection<ContextQuestion> */
            $qs = $this->questions->merge($questions);
            $this->questions = $qs;
        }
    }

    private function addQuestions(): void
    {
        $this->questions = $this->questions->sortBy('category_id');
        foreach ($this->questions as $index => $question) {
            $row = ((int) $index) + 2;
            $this->questionRowMap[$question->id] = $row;
            $this->sheet->setCellValue('A' . $row, $question->id);
            $this->sheet->setCellValue('B' . $row, $question->toQuestion());
            $descriptions = $question->descriptions->filter(function ($d) {
                /** @var ContextQuestionDescription $d */
                return in_array($d->location_id, $this->countryLocations);
            });
            $str = '';
            foreach ($descriptions as $d) {
                $str .= ' ' . strip_tags($d->description ?? '');
            }
            $this->sheet->setCellValue('C' . $row, trim($str));

            $progressTotal = $this->questions->count();
            $this->updateProgress($progressTotal);
        }
    }

    private function formatting(): void
    {
        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setWidth(40);
        $this->sheet->getRowDimension(1)->setRowHeight(100);
        $this->sheet->getStyle('1')->getAlignment()->setWrapText(true);
        $this->sheet->freezePane('A2');
    }

    private function populateAnswers(): void
    {
        $libryos = $this->getLibryosQuery()
            ->with(['contextQuestions'])
            ->cursor();

        foreach ($libryos as $index => $libryo) {
            /** @var Libryo $libryo */
            $col = Coordinate::stringFromColumnIndex(((int) $index) + static::LIBRYO_COLS_START_NUM);
            foreach ($this->questions as $question) {
                /** @var ContextQuestion $question */
                if (!isset($this->questionRowMap[$question->id])) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                if (isset($this->questionPlaceMap[$libryo->id]) && in_array($question->id, $this->questionPlaceMap[$libryo->id])) {
                    /** @var ContextQuestion $qu */
                    $qu = $libryo->contextQuestions->find($question->id);
                    $answer = ['No', 'Yes', 'Unanswered'][$qu->pivot->answer] ?? ''; // @phpstan-ignore-line
                } else {
                    $answer = 'N/A';
                }
                $row = $this->questionRowMap[$question->id];

                $this->setAnswerDropdown($col . $row);
                $this->sheet->setCellValue($col . $row, $answer);
            }
        }
    }

    private function setAnswerDropdown(string $coordinate): void
    {
        $validation = $this->sheet->getCell($coordinate)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Please select a value from the list.');
        $validation->setFormula1('"Yes,No,Unanswered,N/A"');
    }

    private function setProtectedCells(): void
    {
        $this->sheet->getProtection()->setPassword('libryo super secret password');
        $this->sheet->getProtection()->setSheet(true);
        $this->sheet->getStyle('1')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
        $this->sheet->getStyle('A:C')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);

        $lastCol = $this->sheet->getHighestColumn('1');
        $lastRow = $this->sheet->getHighestRow('A');
        $range = static::LIBRYO_COLS_START_COL . '2:' . $lastCol . $lastRow;
        $this->sheet->getStyle($range)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
    }

    /**
     * Update progress.
     *
     * @param int $progressTotal
     *
     * @return void
     */
    protected function updateProgress(int $progressTotal): void
    {
        if (!$this->progressCallback) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        ++$this->progress;
        call_user_func_array($this->progressCallback, [
            ($this->progress / $progressTotal) * 100,
        ]);
    }
}
