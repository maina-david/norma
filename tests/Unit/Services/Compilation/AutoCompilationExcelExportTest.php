<?php

namespace Tests\Unit\Services\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Compilation\ContextQuestion;
use App\Models\Compilation\ContextQuestionDescription;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Services\Compilation\AutoCompilationExcelExport;
use Tests\TestCase;

class AutoCompilationExcelExportTest extends TestCase
{
    public function testBuild(): void
    {
        $org = Organisation::factory()->create();
        $norma = Norma::factory()->for($org)->create();
        $norma2 = Norma::factory()->for($org)->for($norma->location)->create();
        // different location and domain, so should be N/A answers
        $norma3 = Norma::factory()->for($org)->create();
        $domain = LegalDomain::factory()->create();
        $domain->update(['top_parent_id' => $domain->id]);
        $norma->legalDomains()->attach($domain);
        $norma2->legalDomains()->attach($domain);

        $question = ContextQuestion::factory()->create();
        $reference1 = Reference::factory()->create();
        $reference1->contextQuestions()->attach($question);
        $reference1->locations()->attach($norma->location);
        $reference1->legalDomains()->attach($domain);
        $question2 = ContextQuestion::factory()->create();
        $reference2 = Reference::factory()->create();
        $reference2->contextQuestions()->attach($question2);
        $reference2->locations()->attach($norma->location);
        $reference2->legalDomains()->attach($domain);

        // a question that should be part of the norma but isn't should still be in the export
        $question3 = ContextQuestion::factory()->create();
        $reference3 = Reference::factory()->create();
        $reference3->contextQuestions()->attach($question3);
        $reference3->locations()->attach($norma->location);
        $reference3->legalDomains()->attach($domain);

        $norma->location->update(['location_country_id' => $norma->location->id]);

        $description = ContextQuestionDescription::factory()->for($question)->for($norma->location)->create();
        $description2 = ContextQuestionDescription::factory()->for($question2)->for($norma->location)->create();
        $norma->contextQuestions()->attach($question->id, ['answer' => ContextQuestionAnswer::yes()->value]);
        $norma->contextQuestions()->attach($question2->id, ['answer' => ContextQuestionAnswer::no()->value]);
        $norma2->contextQuestions()->attach($question->id, ['answer' => ContextQuestionAnswer::no()->value]);

        $spreadsheet = app(AutoCompilationExcelExport::class)->build($org, null, fn () => null);
        $ws = $spreadsheet->getSheet(0);
        $this->assertSame($norma->id, (int) explode(':', $ws->getCell('D1')->getValue())[0]);
        $this->assertSame($norma2->id, (int) explode(':', $ws->getCell('E1')->getValue())[0]);

        // q1 norma1
        $this->assertSame('Yes', $ws->getCell('D2')->getValue());
        // q2 norma1
        $this->assertSame('No', $ws->getCell('D3')->getValue());
        // q1 norma2
        $this->assertSame('No', $ws->getCell('E2')->getValue());
        // q2 norma2
        $this->assertSame('Unanswered', $ws->getCell('E3')->getValue());
        // the question that wasn't part of the norma should now have been added and defaulted to no
        $this->assertSame('Unanswered', $ws->getCell('D4')->getValue());

        $this->assertSame($question->id, $ws->getCell('A2')->getValue());
        $this->assertSame($question2->id, $ws->getCell('A3')->getValue());
        $this->assertSame($question->toQuestion(), $ws->getCell('B2')->getValue());
        $this->assertSame($question2->toQuestion(), $ws->getCell('B3')->getValue());
        $this->assertSame($description->description, $ws->getCell('C2')->getValue());

        // norma3 with different location and domain, so should be N/A answers
        $this->assertSame('N/A', $ws->getCell('F4')->getValue());
    }
}
