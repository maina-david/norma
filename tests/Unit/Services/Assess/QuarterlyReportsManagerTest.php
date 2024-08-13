<?php

namespace Tests\Unit\Services\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\AssessSnapshot;
use App\Models\Customer\Libryo;
use App\Services\Assess\QuarterlyReportsManager;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QuarterlyReportsManagerTest extends TestCase
{
    public function testgetResponseData(): void
    {
        $manager = app(QuarterlyReportsManager::class);
        $libryo = Libryo::factory()->create();
        $snapshot = AssessSnapshot::factory()->for($libryo)->create([
            'month_date' => Carbon::now()->startOfMonth()->subQuarter()->endOfQuarter(),
            'total' => 4,
            'total_' . ResponseStatus::yes()->value => 1,
            'total_' . ResponseStatus::no()->value => 1,
            'total_' . ResponseStatus::notApplicable()->value => 1,
            'total_' . ResponseStatus::notAssessed()->value => 1,
            'last_answered' => now()->subDays(20),
        ]);
        $responseYes = AssessmentItemResponse::factory()->for($libryo)->create(['answer' => ResponseStatus::yes()->value]);
        $responseNo = AssessmentItemResponse::factory()->for($libryo)->create(['answer' => ResponseStatus::no()->value]);
        $responseNA = AssessmentItemResponse::factory()->for($libryo)->create(['answer' => ResponseStatus::notApplicable()->value]);
        $responseNotAssessed = AssessmentItemResponse::factory()->for($libryo)->create(['answer' => ResponseStatus::notAssessed()->value]);
        $snapshot->assessmentItemResponses()->attach($responseYes, ['answer' => ResponseStatus::yes()->value, 'risk_rating' => RiskRating::high()->value]);
        $snapshot->assessmentItemResponses()->attach($responseNo, ['answer' => ResponseStatus::no()->value, 'risk_rating' => RiskRating::low()->value]);
        $snapshot->assessmentItemResponses()->attach($responseNA, ['answer' => ResponseStatus::notApplicable()->value, 'risk_rating' => RiskRating::medium()->value]);
        $snapshot->assessmentItemResponses()->attach($responseNotAssessed, ['answer' => ResponseStatus::notAssessed()->value, 'risk_rating' => RiskRating::notRated()->value]);

        $data = $manager->getResponseData(Libryo::whereKey($libryo->id), AssessmentItemResponse::forLibryo($libryo));

        $this->assertArrayHasKey('chart_data', $data);
        $this->assertArrayHasKey('table_data', $data);
        $this->assertSame(4, $data['table_data']['quarter_2']['total']);
        $this->assertSame(1, $data['chart_data']['quarter_2'][ResponseStatus::yes()->value]);
        $this->assertSame(1, $data['chart_data']['quarter_2'][ResponseStatus::no()->value]);
        $this->assertSame(1, $data['chart_data']['quarter_2'][ResponseStatus::notAssessed()->value]);
        $this->assertSame(1, $data['chart_data']['quarter_2'][ResponseStatus::notApplicable()->value]);
    }
}
