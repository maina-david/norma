<?php

namespace Tests\Unit\Services\Corpus;

use App\Enums\Ontology\CategoryType;
use App\Models\Actions\ActionArea;
use App\Models\Arachno\Source;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use App\Models\Ontology\Category;
use App\Models\Ontology\CategoryType as CategoryTypeModel;
use App\Models\Ontology\LegalDomain;
use App\Services\Corpus\IngestBySpreadsheet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\Feature\My\MyTestCase;

class IngestBySpreadsheetTest extends MyTestCase
{
    public function testImportFromExcel(): void
    {
        $spreadsheet = new Spreadsheet();

        /** @var Source */
        $source = Source::factory()->create();
        /** @var Location */
        $location = Location::factory()->create();
        /** @var LegalDomain */
        $domain = LegalDomain::factory()->create();
        /** @var LegalDomain */
        $domain2 = LegalDomain::factory()->create();
        /** @var Work */
        $existingWork = Work::factory()->create();
        /** @var Work */
        $existingRef = Reference::factory()->for($existingWork)->create(['position' => 1]);

        DB::table((new CategoryTypeModel())->getTable())->insert(['id' => CategoryType::SUBJECT->value, 'title' => 'Subject', 'taxonomy_type' => 'bearer_topics']);
        DB::table((new CategoryTypeModel())->getTable())->insert(['id' => CategoryType::CONTROL->value, 'title' => 'Control', 'taxonomy_type' => 'filter_topics']);
        /** @var Category */
        $controlCat1 = Category::factory()->create(['category_type_id' => CategoryType::CONTROL->value]);
        /** @var Category */
        $controlCat2 = Category::factory()->create(['category_type_id' => CategoryType::CONTROL->value]);
        /** @var Category */
        $subjectCat1 = Category::factory()->create(['category_type_id' => CategoryType::SUBJECT->value]);
        /** @var Category */
        $subjectCat2 = Category::factory()->create(['category_type_id' => CategoryType::SUBJECT->value]);
        /** @var ContextQuestion */
        $contextQuestion1 = ContextQuestion::factory()->create();
        /** @var ContextQuestion */
        $contextQuestion2 = ContextQuestion::factory()->create();
        $languageCode = 'deu';
        /** @var ActionArea */
        $actionArea1 = ActionArea::factory()->create(['subject_category_id' => $subjectCat1->id, 'control_category_id' => $controlCat1->id]);

        $sheet0 = $spreadsheet->createSheet(0);
        $sheet = $spreadsheet->createSheet(1);
        $sheet->setTitle('Entry');

        // FIRST WORK
        /** @var Work */
        $work1 = Work::factory()->make();
        $sheet->setCellValue('C2', $work1->title);
        $refTitle1 = $this->faker->unique()->words(3, true);
        $refTitle2 = $this->faker->unique()->words(3, true);
        $refText1 = $this->faker->unique()->paragraphs(3, true);
        $refText1Word = explode(' ', $refText1)[0];
        $sheet->setCellValue('G2', $refTitle1);
        $sheet->setCellValue('H2', $refText1);
        $sheet->setCellValue('I2', 'Yes');
        $sheet->setCellValue('J2', $subjectCat1->display_label . '    |    Some Parent Category');
        $sheet->setCellValue('K2', $controlCat1->display_label);
        // action area doesn't exist for this one, so should go into report
        $sheet->setCellValue('J3', $subjectCat2->display_label . '    |    Some Parent Category');
        $sheet->setCellValue('K3', $controlCat2->display_label);

        $sheet->setCellValue('L2', $contextQuestion1->toQuestion() . '    |    Some Parent Category');
        $sheet->setCellValue('M2', $domain->title);

        // SECOND WORK
        /** @var Work */
        $work2 = Work::factory()->make();
        $sheet->setCellValue('C4', $work2->title);
        $sheet->setCellValue('G4', $refTitle1);
        $sheet->setCellValue('I4', 'Yes');
        $sheet->setCellValue('J4', $subjectCat1->display_label . '    |    Some Parent Category');
        $sheet->setCellValue('K4', $controlCat1->display_label);
        $sheet->setCellValue('M4', $domain->title);
        $sheet->setCellValue('M5', $domain2->title);
        $sheet->setCellValue('G6', $refTitle2);
        $sheet->setCellValue('M6', $domain->title);
        $sheet->setCellValue('M7', $domain2->title);

        // THIRD WORK
        $refTitle3 = $this->faker->unique()->words(3, true);
        $sheet->setCellValue('B8', $existingWork->id);
        $sheet->setCellValue('C8', $existingWork->title);
        $sheet->setCellValue('G8', $refTitle3);
        $sheet->setCellValue('M8', $domain->title);

        $report = app(IngestBySpreadsheet::class)->importFromExcel($spreadsheet, $source->id, $location->id, $languageCode);
        /** @var Work|null */
        $work = Work::where('title', $work1->title)->first();
        $this->assertNotNull($work);
        $this->assertContains($work->id, $report['works_created']);
        $this->assertSame($work->source_id, $source->id);
        $this->assertSame($work->primary_location_id, $location->id);
        $this->assertSame($work->language_code, $languageCode);
        $refs = $work->references()->with(['refPlainText', 'actionAreas', 'contextQuestions', 'legalDomains', 'contentDraft'])->get();
        $this->assertSame($refTitle1, $refs[0]->refPlainText?->plain_text);
        $this->assertStringContainsString($refText1Word, $refs[0]->contentDraft?->html_content);
        $this->assertStringContainsString('<p>', $refs[0]->contentDraft?->html_content);
        $this->assertTrue($refs[0]->actionAreas->contains($actionArea1));
        $this->assertTrue(str_contains($report['missing_action_areas'][0]['subject'], $subjectCat2->display_label));
        $this->assertTrue(str_contains($report['missing_action_areas'][0]['control'], $controlCat2->display_label));

        $this->assertTrue($refs[0]->contextQuestions->contains($contextQuestion1));
        $this->assertTrue($refs[0]->legalDomains->contains($domain));

        /** @var Work|null */
        $work2 = Work::where('title', $work2->title)->first();
        $this->assertNotNull($work2);
        /** @var Collection<Reference> */
        $refs = $work2->references()->with(['refPlainText', 'actionAreas', 'contextQuestions', 'legalDomains', 'locations'])->get();
        $this->assertSame(2, $refs->count());
        $this->assertTrue($refs[0]->legalDomains->contains($domain));
        $this->assertTrue($refs[0]->legalDomains->contains($domain2));
        $this->assertTrue($refs[1]->legalDomains->contains($domain));
        $this->assertTrue($refs[1]->legalDomains->contains($domain2));
        $this->assertTrue($refs[1]->locations->contains($location));

        /** @var Collection<Reference> */
        $refs = $existingWork->references()->with(['refPlainText', 'actionAreas', 'contextQuestions', 'legalDomains', 'locations'])->get();
        $this->assertTrue($refs[1]->legalDomains->contains($domain));
        // WORK ID was provided, so let's make sure it doesn't create a new work.
        $this->assertFalse(Work::where('title', $existingWork->title)->count() > 1);
        $this->assertTrue($refs[1]->position === 2);
    }
}
