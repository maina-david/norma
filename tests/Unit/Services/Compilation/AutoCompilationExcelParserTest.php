<?php

namespace Tests\Unit\Services\Compilation;

use App\Services\Compilation\AutoCompilationExcelParser;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class AutoCompilationExcelParserTest extends TestCase
{
    public function testParse(): void
    {
        $spreadsheet = $this->createSpreadsheet();

        $this->partialMock(AutoCompilationExcelParser::class, function (MockInterface $mock) use ($spreadsheet) {
            $mock->shouldReceive('setupReader')
                ->andReturn($spreadsheet);
        });
        $service = app(AutoCompilationExcelParser::class);

        $service->setSheet($spreadsheet->getSheet(0));

        $answers = $service->parse(Str::random(15));

        $this->assertSame(1, $answers[1][1]);
        $this->assertSame(0, $answers[2][1]);

        $this->assertSame(0, $answers[1][2]);
        $this->assertSame(1, $answers[2][2]);

        $this->assertSame(0, $answers[1][3]);
        $this->assertTrue(!isset($answers[2][3]));

        $this->assertSame(2, $answers[1][4]);
        $this->assertSame(2, $answers[2][4]);
    }

    private function createSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $ws = new Worksheet($spreadsheet);
        $spreadsheet->addSheet($ws, 0);
        for ($i = 0; $i < 10; $i++) {
            $ws->insertNewColumnBefore('A');
        }

        $ws->setCellValue('D1', 1);
        $ws->setCellValue('E1', 2);
        $ws->setCellValue('A2', 1);
        $ws->setCellValue('A3', 2);
        $ws->setCellValue('A4', 3);
        $ws->setCellValue('A5', 4);

        $ws->setCellValue('D2', 1);
        $ws->setCellValue('E2', 0);

        $ws->setCellValue('D3', 'No');
        $ws->setCellValue('E3', ' YeS   ');

        $ws->setCellValue('D4', 0);
        $ws->setCellValue('E4', 'bla');
        $ws->setCellValue('D5', 2);
        $ws->setCellValue('E5', 'maybe');

        return $spreadsheet;
    }
}
