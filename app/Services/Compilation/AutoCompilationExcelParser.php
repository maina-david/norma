<?php

namespace App\Services\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class AutoCompilationExcelParser
{
    public const LIBRYO_COLS_START_COL = 'D';
    public const LIBRYO_COLS_START_ROW = '1';

    /** @var string */
    protected $filePath;

    /** @var Spreadsheet */
    protected $spreadsheet;

    /** @var Worksheet */
    protected $sheet;

    /** @var array<int, mixed> */
    protected $rows = [];

    /** @var array<int> */
    protected $libryos = [];

    /** @var array<int, array<int, int>> */
    protected $answers = [];

    /**
     * Import the items in the uploaded excel file.
     *
     * @param string $filePath
     *
     * @return array<int, array<int, int>>
     */
    public function parse(string $filePath): array
    {
        $this->filePath = $filePath;

        $this->execute();

        return $this->answers;
    }

    /**
     * Execute the importation process.
     */
    private function execute(): void
    {
        if (!$this->setupReader()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $this->extractLibryos();

        $this->extractRows();

        $this->parseRows();

        $this->clean();
    }

    /**
     * @codeCoverageIgnore
     * Setup the excel reader.
     *
     * @throws RuntimeException
     *
     * @return Spreadsheet|bool
     */
    public function setupReader(): Spreadsheet|bool
    {
        if (!Storage::exists($this->filePath)) {
            return false;
        }

        /** @var string $content */
        $content = Storage::get($this->filePath);

        Storage::disk('local')->put($this->filePath, $content);

        try {
            $this->spreadsheet = IOFactory::load(storage_path('app/' . $this->filePath));
            $this->sheet = $this->spreadsheet->getSheet(0);

            if ($this->getSheet()->getCell('A1')->getValue() !== 'id') {
                throw new RuntimeException('File does not have correct header format');
            }
        } catch (Exception $ex) {
            $this->clean();

            throw new RuntimeException('The uploaded file is not formatted correctly');
        }

        return $this->spreadsheet;
    }

    public function getSheet(): Worksheet
    {
        return $this->sheet;
    }

    public function setSheet(Worksheet $sheet): void
    {
        $this->sheet = $sheet;
    }

    /**
     * Extract all the libryo's that are being compiled.
     *
     * @return void
     */
    private function extractLibryos(): void
    {
        $start = static::LIBRYO_COLS_START_COL . static::LIBRYO_COLS_START_ROW;
        $range = $start . ':' . $this->getSheet()->getHighestColumn(static::LIBRYO_COLS_START_ROW) . static::LIBRYO_COLS_START_ROW;
        $cols = $this->getSheet()->rangeToArray($range)[0];
        $this->libryos = [];
        foreach ($cols as $col) {
            $id = explode(':', $col)[0] ?? null;
            if (!$id || !is_numeric($id)) {
                continue;
            }
            $this->libryos[] = (int) $id;
        }
    }

    /**
     * Extract the rows from the excel.
     *
     * @return array<int, mixed>
     */
    private function extractRows(): array
    {
        $range = 'A2:' . $this->getSheet()->getHighestColumn('1') . $this->getSheet()->getHighestRow('A');
        $this->rows = $this->getSheet()->rangeToArray($range);

        $this->rows = array_map(function ($item) {
            foreach (array_keys($item) as $key) {
                $item[$key] = $item[$key];
            }

            return $item;
        }, $this->rows);

        return $this->rows;
    }

    /**
     * Extracts the answers for each row.
     *
     * @return void
     */
    private function parseRows(): void
    {
        foreach ($this->rows as $row) {
            $id = (int) $row[0];
            foreach ($this->libryos as $key => $libryoId) {
                if (!isset($this->answers[$libryoId])) {
                    $this->answers[$libryoId] = [];
                }

                $answer = $row[$key + 3];
                switch (trim(strtolower($answer))) {
                    case 'yes':
                        $answer = ContextQuestionAnswer::yes()->value;
                        break;
                    case 'no':
                        $answer = ContextQuestionAnswer::no()->value;
                        break;
                    case 'maybe':
                    case 'unanswered':
                        $answer = ContextQuestionAnswer::maybe()->value;
                        break;
                    default:
                        break;
                }

                if (is_numeric($answer)) {
                    $this->answers[$libryoId][$id] = (int) $answer;
                }
            }
        }
    }

    /**
     * Remove the uploaded file.
     *
     * @return void
     */
    private function clean(): void
    {
        Storage::disk('local')->delete($this->filePath);
    }
}
