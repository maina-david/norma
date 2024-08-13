<?php

namespace Tests\Unit\Exports\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Exports\Assess\AssessResponsesExcelExport;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class AssessResponsesExcelExportTest extends TestCase
{
    use CompilesStream;

    public function testBuild(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $user = User::factory()->create();
        $user->libryos()->attach($libryo);

        $items = AssessmentItem::factory(5)->create(['risk_rating' => RiskRating::high()->value]);
        $item = AssessmentItemResponse::factory()->for($libryo)->for($items[0])->create(['answer' => ResponseStatus::no()->value]);
        $item->assessmentItem->update(['legal_domain_id' => $domain->id]);

        AssessmentItemResponse::factory()->for($libryo)->for($items[1])->create(['answer' => ResponseStatus::notApplicable()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[2])->create(['answer' => ResponseStatus::notAssessed()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[3])->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[3])->create(['answer' => ResponseStatus::yes()->value]);

        $progressCallback = function ($progress) {
        };

        $filters = [
            'domains' => [$item->assessmentItem->legalDomain->id],
        ];

        $libryo->update(['title' => 'With - Hyphens - ' . $libryo->title]);

        app(AssessResponsesExcelExport::class)->export($user, $organisation, $libryo, $filters, $progressCallback);

        $excel = app(AssessResponsesExcelExport::class)->export($user, $organisation, null, $filters, $progressCallback);

        $ws = $excel->getActiveSheet();

        $this->assertStringNotContainsString('With', $ws->getTitle());

        $this->assertSame('Assessment Items', $ws->getCell('B1')->getValue());
        $this->assertSame($item->assessmentItem->description, $ws->getCell('B2')->getValue());
        $this->assertSame($item->assessmentItem->legalDomain->title, $ws->getCell('E2')->getValue());
        $this->assertSame($item->assessmentItem->id, $ws->getCell('A2')->getValue());
    }
}
