<?php

namespace Tests\Unit\Exports\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Exports\Assess\AssessMetricsExcelExport;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use Artisan;
use Tests\TestCase;
use Tests\Traits\CompilesStream;

class AssessMetricsExcelExportTest extends TestCase
{
    use CompilesStream;

    public function testBuild(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $this->travelTo(now()->subQuarter());

        $user = User::factory()->create();
        $user->libryos()->attach($libryo);

        $items = AssessmentItem::factory(5)->create(['risk_rating' => RiskRating::high()->value]);
        $items->push(AssessmentItem::factory()->create(['risk_rating' => RiskRating::medium()->value]));
        $items->push(AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]));
        $items->push(AssessmentItem::factory()->create(['risk_rating' => RiskRating::notRated()->value]));
        AssessmentItemResponse::factory()->for($libryo)->for($items[0])->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[1])->create(['answer' => ResponseStatus::notApplicable()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[2])->create(['answer' => ResponseStatus::notAssessed()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[3])->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[3])->create(['answer' => ResponseStatus::yes()->value]);

        AssessmentItemResponse::factory()->for($libryo)->for($items[4])->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[5])->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[6])->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($libryo)->for($items[7])->create(['answer' => ResponseStatus::no()->value]);

        $this->travelBack();

        Artisan::call('once:assess-create-historic-snapshots');

        $progressCallback = function ($progress) {
        };

        $filters = [];
        $excel = app(AssessMetricsExcelExport::class)->forOrganisation($organisation, null, $user, $filters, $progressCallback);

        $ws = $excel->getActiveSheet();
        $this->assertSame('Risk Rating', $ws->getCell('A1')->getValue());
        $this->assertSame('High', $ws->getCell('A2')->getValue());
        $this->assertSame($libryo->title, $ws->getCell('B2')->getValue());
        $this->assertSame(1, $ws->getCell('D2')->getValue()); // yes
        $this->assertSame(6, $ws->getCell('E2')->getValue()); // no
        $this->assertSame(1, $ws->getCell('F2')->getValue()); // not applicable
        $this->assertSame(1, $ws->getCell('G2')->getValue()); // not assessessed
        $this->assertSame(3, $ws->getCell('H2')->getValue()); // high non-compliant
        $this->assertSame('67%', $ws->getCell('I2')->getValue()); // non-compliant items

        $excel = app(AssessMetricsExcelExport::class)->forOrganisation($organisation, null, $user, ['search' => $libryo->title], $progressCallback);
        $ws = $excel->getActiveSheet();
        $this->assertSame($libryo->title, $ws->getCell('B2')->getValue());
    }
}
