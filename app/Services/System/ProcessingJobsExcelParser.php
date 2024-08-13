<?php

namespace App\Services\System;

use App\Enums\System\ProcessingJobType;
use App\Models\Arachno\Spider;
use App\Models\Corpus\CatalogueWorkExpression;
use App\Models\System\ProcessingJob;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;
use Throwable;

/**
 * @codeCoverageIgnore
 * We'll code coverage ignore the whole thing as we'll hopefully get rid of this
 */
class ProcessingJobsExcelParser
{
    /** @var string */
    protected $filePath;

    /** @var Spreadsheet */
    protected $spreadsheet;

    /** @var Worksheet */
    protected $sheet;

    /** @var array<int, mixed> */
    protected $rows = [];

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

        return $this->rows;
    }

    /**
     * Execute the importation process.
     */
    private function execute(): void
    {
        if (!$this->setupReader()) {
            return;
        }

        foreach ($this->extractRows() as $row) {
            if (!Spider::where('slug', $row[0])->exists()) {
                continue;
            }
            if (!in_array($row[1], [ProcessingJobType::arachnoCapture()->value, ProcessingJobType::arachnoRecapture()->value])) {
                continue;
            }
            if (isset($row[4]) && !CatalogueWorkExpression::where('id', $row[4])->exists()) {
                continue;
            }

            $availableAt = null;
            try {
                $availableAt = isset($row[3]) && $row[3] ? Carbon::parse((string) $row[3]) : null;

                ProcessingJob::create([
                    'spider_slug' => $row[0],
                    'job_type' => $row[1],

                    'payload' => [
                        'source_id' => $row[0],
                        'spider_slug' => $row[0],
                        'start_url' => $row[2],
                    ],
                    'available_at' => $availableAt,
                    'catalogue_work_expression_id' => $row[4] ?? null,
                ]);
            } catch (Throwable $th) {
                // throw $th;
            }
        }

        $this->clean();
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
     * @codeCoverageIgnore
     * Setup the excel reader.
     *
     * @throws RuntimeException
     *
     * @return Spreadsheet|bool
     */
    public function setupReader(): Spreadsheet|bool
    {
        try {
            $this->spreadsheet = IOFactory::load($this->filePath);
            $this->setSheet($this->spreadsheet->getSheet(0));

            if ($this->getSheet()->getCell('A1')->getValue() !== 'spider_slug') {
                throw new RuntimeException('File does not have correct header format');
            }
        } catch (Exception $ex) {
            $this->clean();

            throw new RuntimeException($ex->getMessage());
            // throw new RuntimeException('The uploaded file is not formatted correctly');
        }

        return $this->spreadsheet;
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
     * Remove the uploaded file.
     *
     * @return void
     */
    private function clean(): void
    {
        Storage::disk('local')->delete($this->filePath);
    }
}
