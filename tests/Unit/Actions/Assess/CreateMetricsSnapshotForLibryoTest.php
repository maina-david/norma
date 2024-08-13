<?php

namespace Tests\Unit\Actions\Assess;

use App\Actions\Assess\CreateMetricsSnapshotForLibryo;
use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\AssessSnapshot;
use App\Models\Customer\Libryo;
use App\Services\Assess\AssessStatsService;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateMetricsSnapshotForLibryoTest extends TestCase
{
    public function testHandle(): void
    {
        $testData = [
            'risk_rating' => RiskRating::high(),
            'last_answered' => now()->subMonths(5),
            'total_count' => 31,
            'total_' . ResponseStatus::yes()->value => 4,
            'total_' . ResponseStatus::no()->value => 20,
            'total_' . ResponseStatus::notAssessed()->value => 0,
            'total_' . ResponseStatus::notApplicable()->value => 7,
            'total_non_compliant_items_' . RiskRating::high()->value => 15,
            'total_non_compliant_items_' . RiskRating::medium()->value => 5,
            'total_non_compliant_items_' . RiskRating::low()->value => 0,
            'percentage_non_compliant_items' => 20.0, // just made something up here...
        ];
        $this->partialMock(AssessStatsService::class, function (MockInterface $mock) use ($testData) {
            $mock->shouldReceive('getRiskMetricsForResponses')
                ->andReturn($testData);
        });

        $action = app(CreateMetricsSnapshotForLibryo::class);
        $libryo = Libryo::factory()->create();
        $aiItem = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($aiItem)->create(['answer' => ResponseStatus::no()->value]);

        $count = AssessSnapshot::count();

        $action->asJob($libryo->id, now()->subMonth()->endOfMonth()->format('Y-m-d H:i:s'));
        $this->assertGreaterThan($count, AssessSnapshot::count());

        $snapshot = AssessSnapshot::with('assessmentItemResponses')->get()->last();
        $this->assertSame($testData['risk_rating']->value, $snapshot->risk_rating);
        $this->assertTrue($testData['last_answered']->is($snapshot->last_answered));
        $this->assertSame($testData['total_' . ResponseStatus::yes()->value], $snapshot['total_' . ResponseStatus::yes()->value]);
        $this->assertSame($testData['total_' . ResponseStatus::no()->value], $snapshot['total_' . ResponseStatus::no()->value]);
        $this->assertSame($testData['total_' . ResponseStatus::notAssessed()->value], $snapshot['total_' . ResponseStatus::notAssessed()->value]);
        $this->assertSame($testData['total_' . ResponseStatus::notApplicable()->value], $snapshot['total_' . ResponseStatus::notApplicable()->value]);
        $this->assertSame($testData['total_non_compliant_items_' . RiskRating::high()->value], $snapshot['total_non_compliant_items_' . RiskRating::high()->value]);
        $this->assertSame($testData['total_non_compliant_items_' . RiskRating::medium()->value], $snapshot['total_non_compliant_items_' . RiskRating::medium()->value]);
        $this->assertSame($testData['total_non_compliant_items_' . RiskRating::low()->value], $snapshot['total_non_compliant_items_' . RiskRating::low()->value]);
        $this->assertSame($testData['percentage_non_compliant_items'], $snapshot->non_compliant_percentage);

        $response = $snapshot->assessmentItemResponses->first();
        $this->assertSame(ResponseStatus::no()->value, $response->pivot->answer);
        $this->assertSame(RiskRating::high()->value, $response->pivot->risk_rating);
    }
}
