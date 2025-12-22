<?php

namespace Tests\Unit\Exports\Requirements;

use App\Exports\Requirements\LegalReportExcelExport;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\Work;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class LegalReportExcelExportTest extends TestCase
{
    use CompilesStream;

    public function testBuild(): void
    {
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $childWork = Work::factory()->has(Reference::factory()->count(5))->create();
        $work->children()->attach($childWork);

        $childWork->references->first()->normas()->attach($norma);

        $query = Work::primaryForNorma($norma)
            ->with(['children', 'children.references.citation', 'references.citation', 'references.htmlContent', 'children.references.htmlContent', 'references.refPlainText', 'children.references.refPlainText']);

        $works = $query->get();
        $reference = $works[0]->references->first();
        $reference->htmlContent()->save(new ReferenceContent(['reference_id' => $reference->id, 'cached_content' => $this->faker->randomHtml(1, 2)]));
        // to test a long citation with more than 3500 chars - faker generater 3500 words, but just to make sure
        // it's definitely more than 3500 chars (a word is at min one char)
        $reference2 = $works[0]->references[1];
        $reference2->htmlContent()->save(new ReferenceContent(['reference_id' => $reference->id, 'cached_content' => $this->faker->sentence(3500)]));

        $excel = app(LegalReportExcelExport::class)->setDomain('https://my.norma.com/')->build($query);
        $writer = new Xlsx($excel);
        $localTmpPath = storage_path('app/tmp') . '/report.xlsx';
        $writer->save($localTmpPath);

        $ws = $excel->getSheet(1);
        $cell = $ws->getCell('A1');
        $this->assertSame($works[0]->title, $cell->getValue());

        $this->assertSame($reference->refPlainText?->plain_text, $ws->getCell('A2')->getValue());
        $reference->load(['htmlContent']);
        $reference2->load(['htmlContent']);
        $this->assertStringContainsString(substr(strip_tags($reference->htmlContent->cached_content), 0, 7), $ws->getCell('B2')->getValue());

        $this->assertStringContainsString(substr(strip_tags($reference2->htmlContent->cached_content), 0, 7), $ws->getCell('B3')->getValue());

        $this->assertSame($reference2->refPlainText?->plain_text, $ws->getCell('A3')->getValue());
        // there should be multiple rows of content, so title column is merged across the rows
        $this->assertNull($ws->getCell('A4')->getValue());
        unlink($localTmpPath);
    }
}
