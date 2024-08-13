<?php

namespace Tests\Unit\Services\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use App\Services\Assess\AssessStatsService;
use Tests\Feature\My\MyTestCase;

class AssessStatsServiceTest extends MyTestCase
{
    private function setupServiceLibryoAndItem(): array
    {
        $service = app(AssessStatsService::class);
        $libryo = Libryo::factory()->create();
        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);

        return [$service, $libryo, $item];
    }

    public function testGetRiskLevelNotRatedNoActivity(): void
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::notRated()->is($results['risk_rating']));
    }

    public function testGetRiskLevelHighNoAnswerPercentage(): void
    {
        [$service, $libryo] = $this->setupHighNoAnswerPercentage();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::high()->is($results['risk_rating']));
    }

    public function testGetRiskLevelHighLastActivity(): void
    {
        [$service, $libryo] = $this->setupHighLastActivity();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::high()->is($results['risk_rating']));
    }

    public function testGetRiskLevelHighNoAnswerToHighRisk(): void
    {
        [$service, $libryo] = $this->setupHighNoAnswerToHighRisk();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::high()->is($results['risk_rating']));
    }

    public function testGetRiskLevelHighNotAssessed(): void
    {
        [$service, $libryo] = $this->setupHighNotAssessed();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::high()->is($results['risk_rating']));
    }

    public function testGetRiskLevelMediumNoAnswerPercentage(): void
    {
        [$service, $libryo] = $this->setupMediumNoAnswerPercentage();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));

        $this->assertTrue(RiskRating::medium()->is($results['risk_rating']));
    }

    public function testGetRiskLevelMediumLastActivity(): void
    {
        [$service, $libryo] = $this->setupMediumLastActivity();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::medium()->is($results['risk_rating']));
    }

    public function testGetRiskLevelMediumNoAnswerToMediumRisk(): void
    {
        [$service, $libryo] = $this->setupMediumNoAnswerToMediumRisk();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));

        $this->assertTrue(RiskRating::medium()->is($results['risk_rating']));
    }

    public function testGetRiskLevelMediumNotAssessed(): void
    {
        [$service, $libryo] = $this->setupMediumNotAssessed();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::medium()->is($results['risk_rating']));
    }

    public function testGetRiskLevelLow(): void
    {
        [$service, $libryo] = $this->setupLowRisk();
        $results = $service->getRiskMetricsForResponses(AssessmentItemResponse::whereRelation('libryo', 'id', $libryo->id));
        $this->assertTrue(RiskRating::low()->is($results['risk_rating']));
    }

    public function testBuildRiskRatingQuery(): void
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $item->update(['risk_rating' => RiskRating::high()->value]);
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::high()->value);
    }

    public function testBuildRiskRatingQueryLow(): void
    {
        [$service, $libryo] = $this->setupLowRisk();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::low()->value);
    }

    public function testBuildRiskRatingQueryMediumNotAssessed(): void
    {
        [$service, $libryo] = $this->setupMediumNotAssessed();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::medium()->value);
    }

    public function testBuildRiskRatingQueryMediumNoAnswerToMediumRisk(): void
    {
        [$service, $libryo] = $this->setupMediumNoAnswerToMediumRisk();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::medium()->value);
    }

    public function testBuildRiskRatingQueryMediumLastActivity(): void
    {
        [$service, $libryo] = $this->setupMediumLastActivity();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::medium()->value);
    }

    public function testBuildRiskRatingQueryMediumNoAnswerPercentage(): void
    {
        [$service, $libryo] = $this->setupMediumNoAnswerPercentage();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::medium()->value);
    }

    public function testBuildRiskRatingQueryHighNotAssessed(): void
    {
        [$service, $libryo] = $this->setupHighNotAssessed();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::high()->value);
    }

    public function testBuildRiskRatingQueryHighNoAnswerToHighRisk(): void
    {
        [$service, $libryo] = $this->setupHighNoAnswerToHighRisk();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::high()->value);
    }

    public function testBuildRiskRatingQueryHighLastActivity(): void
    {
        [$service, $libryo] = $this->setupHighLastActivity();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::high()->value);
    }

    public function testBuildRiskRatingQueryHighNoAnswerPercentage(): void
    {
        [$service, $libryo] = $this->setupHighNoAnswerPercentage();

        $results = Libryo::whereKey($libryo->id)->forAssessMetrics()->get();
        $this->assertTrue($results->first()->risk_rating === RiskRating::high()->value);
    }

    public function testgetResponsesChartData(): void
    {
        $service = app(AssessStatsService::class);
        $data = [
            'total_count' => 10,
            'total_' . ResponseStatus::notAssessed()->value => 1,
            'total_' . ResponseStatus::notApplicable()->value => 1,
            'total_' . ResponseStatus::no()->value => 3,
            'total_' . ResponseStatus::yes()->value => 5,
        ];

        $results = $service->getResponsesChartData($data);
        $no = $results[ResponseStatus::no()->value];
        $yes = $results[ResponseStatus::yes()->value];
        $notApplicable = $results[ResponseStatus::notApplicable()->value];
        $notAssessed = $results[ResponseStatus::notAssessed()->value];

        $this->assertSame(3, $no);
        $this->assertSame(1, $notAssessed);
        $this->assertSame(1, $notApplicable);
        $this->assertSame(5, $yes);

        $results = $service->getResponsesChartData(['total_count' => 0]);
        $this->assertTrue($results[ResponseStatus::no()->value] === 0);
    }

    public function testGetRiskRatingWithNoResponses(): void
    {
        $service = app(AssessStatsService::class);
        $rating = $service->getRiskRating(0, 0, 0, 0, 0, 0);
        $this->assertTrue($rating->value === RiskRating::low()->value);
    }

    // ----------------------------------------------------------------------//
    // ----------------------------------------------------------------------//
    // ----------------------------------------------------------------------//
    private function setupLowRisk(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $item2 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);

        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::yes()->value]);
        $response2 = AssessmentItemResponse::factory()->for($libryo)->for($item2)->create(['answer' => ResponseStatus::yes()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        return [$service, $libryo];
    }

    private function setupMediumNotAssessed(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $item2 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);
        $item3 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);

        // between 0-49% not assessed
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::notAssessed()->value]);
        $response2 = AssessmentItemResponse::factory()->for($libryo)->for($item2)->create(['answer' => ResponseStatus::yes()->value]);
        $response3 = AssessmentItemResponse::factory()->for($libryo)->for($item3)->create(['answer' => ResponseStatus::yes()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        return [$service, $libryo];
    }

    private function setupMediumNoAnswerToMediumRisk(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $item->update(['risk_rating' => RiskRating::medium()->value]);
        $item2 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::medium()->value]);
        $item3 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::medium()->value]);
        $item4 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::medium()->value]);
        $item5 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::medium()->value]);
        $items = AssessmentItem::factory(10)->create(['risk_rating' => RiskRating::medium()->value]);

        // 5 or more no answers to medium risk
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);
        $response2 = AssessmentItemResponse::factory()->for($libryo)->for($item2)->create(['answer' => ResponseStatus::no()->value]);
        $response3 = AssessmentItemResponse::factory()->for($libryo)->for($item3)->create(['answer' => ResponseStatus::no()->value]);
        $response4 = AssessmentItemResponse::factory()->for($libryo)->for($item4)->create(['answer' => ResponseStatus::no()->value]);
        $response5 = AssessmentItemResponse::factory()->for($libryo)->for($item5)->create(['answer' => ResponseStatus::no()->value]);

        AssessmentItemResponse::factory(10)->for($libryo)->sequence(function ($seq) use ($items) {
            return ['assessment_item_id' => $items[$seq->index]->id];
        })->create(['answer' => ResponseStatus::yes()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        return [$service, $libryo];
    }

    private function setupMediumLastActivity(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::yes()->value]);
        // 8 months ago activity
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create(['created_at' => now()->subMonths(4)]);

        return [$service, $libryo];
    }

    private function setupMediumNoAnswerPercentage(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $item2 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);
        $item3 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);

        // between 15 and 39% no response
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);
        $response2 = AssessmentItemResponse::factory()->for($libryo)->for($item2)->create(['answer' => ResponseStatus::yes()->value]);
        $response3 = AssessmentItemResponse::factory()->for($libryo)->for($item3)->create(['answer' => ResponseStatus::yes()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        return [$service, $libryo];
    }

    private function setupHighNotAssessed(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $item2 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);
        $item3 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);

        // more than 50% not assessed
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::notAssessed()->value]);
        $response2 = AssessmentItemResponse::factory()->for($libryo)->for($item2)->create(['answer' => ResponseStatus::notAssessed()->value]);
        $response3 = AssessmentItemResponse::factory()->for($libryo)->for($item3)->create(['answer' => ResponseStatus::yes()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        return [$service, $libryo];
    }

    private function setupHighNoAnswerToHighRisk(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $item->update(['risk_rating' => RiskRating::high()->value]);
        $item2 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);
        $item3 = AssessmentItem::factory()->create(['risk_rating' => RiskRating::low()->value]);

        // more than 40% answered yes, but the one high risk item is marked no
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);
        $response2 = AssessmentItemResponse::factory()->for($libryo)->for($item2)->create(['answer' => ResponseStatus::yes()->value]);
        $response3 = AssessmentItemResponse::factory()->for($libryo)->for($item3)->create(['answer' => ResponseStatus::yes()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        return [$service, $libryo];
    }

    private function setupHighLastActivity(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::yes()->value]);
        // 8 months ago activity
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create(['created_at' => now()->subMonths(8)]);

        return [$service, $libryo];
    }

    private function setupHighNoAnswerPercentage(): array
    {
        [$service, $libryo, $item] = $this->setupServiceLibryoAndItem();
        $response = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);
        $activity = AssessmentActivity::factory()->for($response)->for($item)->for($libryo)->create();

        return [$service, $libryo];
    }
}
