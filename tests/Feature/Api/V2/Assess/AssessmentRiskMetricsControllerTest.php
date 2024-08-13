<?php

namespace Tests\Feature\Api\V2\Assess;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use Tests\Feature\Api\ApiTestCase;

class AssessmentRiskMetricsControllerTest extends ApiTestCase
{
    public function testMetricsForLibryos(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $aItems = AssessmentItem::factory(4)->create(['legal_domain_id' => $domain->id]);
        $aItemsHi = AssessmentItem::factory(4)->create(['legal_domain_id' => $domain->id, 'risk_rating' => RiskRating::high()->value]);
        AssessmentItemResponse::factory()->for($aItems[0])->for($libryo)->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($aItems[1])->for($libryo)->create(['answer' => ResponseStatus::yes()->value]);
        AssessmentItemResponse::factory()->for($aItems[2])->for($libryo)->create(['answer' => ResponseStatus::notApplicable()->value]);
        AssessmentItemResponse::factory()->for($aItems[3])->for($libryo)->create(['answer' => ResponseStatus::notAssessed()->value]);

        AssessmentItemResponse::factory()->for($aItemsHi[0])->for($libryo)->create(['answer' => ResponseStatus::no()->value]);
        AssessmentItemResponse::factory()->for($aItemsHi[1])->for($libryo)->create(['answer' => ResponseStatus::yes()->value]);
        AssessmentItemResponse::factory()->for($aItemsHi[2])->for($libryo)->create(['answer' => ResponseStatus::notApplicable()->value]);
        AssessmentItemResponse::factory()->for($aItemsHi[3])->for($libryo)->create(['answer' => ResponseStatus::notAssessed()->value]);

        $aItemNotRated = AssessmentItem::factory()->create(['legal_domain_id' => $domain->id, 'risk_rating' => RiskRating::notRated()->value]);
        AssessmentItemResponse::factory()->for($aItemNotRated)->for($libryo)->create(['answer' => ResponseStatus::no()->value]);

        $reference = $work->references->load('citation')->first();
        $aItems->each(fn ($sai) => $sai->references()->attach($reference));

        $user = User::factory()->create();
        $user->libryos()->attach($libryo);

        $routeName = 'api.v2.assessment-metrics.for.libryo';
        $route = route($routeName);
        $response = $this->assertApiUnauthorizedThenRun($user, 'post', $route, ['libryos' => [$libryo->id]]);
        $items = [];

        $items[] = [
            'id' => $libryo->id,
            'counts' => [
                'yes' => [
                    'not_rated' => 0,
                    'lo' => 1,
                    'med' => 0,
                    'hi' => 1,
                ],
                'no' => [
                    'not_rated' => 1,
                    'lo' => 1,
                    'med' => 0,
                    'hi' => 1,
                ],
                'not_assessed' => [
                    'not_rated' => 0,
                    'lo' => 1,
                    'med' => 0,
                    'hi' => 1,
                ],
                'not_applicable' => [
                    'not_rated' => 0,
                    'lo' => 1,
                    'med' => 0,
                    'hi' => 1,
                ],
            ],
            'risk' => 'hi',
        ];

        $response->assertJson([
            'data' => $items,
        ], true);

        $this->deleteCompiledStream();
    }
}
