<?php

namespace Tests\Feature\Api\V3\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
use Tests\Feature\Api\V3\ApiV3TestCase;

class AssessmentItemControllerTest extends ApiV3TestCase
{
    public function testAssessmentItemsForOrganisation(): void
    {
        /** @var Norma $norma */
        [$norma, $organisation] = $this->initCompiledStream();

        $otherNorma = Norma::factory()->create(['organisation_id' => $organisation->id]);
        $forNorma = AssessmentItem::factory()->create();
        $forOtherNorma = AssessmentItem::factory()->create();

        $resForNorma = AssessmentItemResponse::factory()->create(['assessment_item_id' => $forNorma->id, 'place_id' => $norma->id])->refresh();
        $resForNorma2 = AssessmentItemResponse::factory()->create(['assessment_item_id' => $forNorma->id, 'place_id' => $otherNorma->id])->refresh();
        $resForOtherNorma = AssessmentItemResponse::factory()->create(['assessment_item_id' => $forOtherNorma->id, 'place_id' => $otherNorma->id])->refresh();

        $responseNorma = [
            'id' => $forNorma->id,
            'description' => $forNorma->toDescription(),
            'risk_rating' => $forNorma->risk_rating,
            'responses' => [
                [
                    'stream_id' => $resForNorma->place_id,
                    'answer' => $resForNorma->answer,
                    'last_answered_at' => $resForNorma->answered_at?->toDateString(),
                    'next_due_at' => $resForNorma->next_due_at?->toDateString(),
                    'frequency' => $resForNorma->frequency,
                    'frequency_interval' => $resForNorma->frequency_interval->value,
                    'link' => route('my.normas.activate.redirect', [
                        'norma' => $resForNorma->place_id,
                        'redirect' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $resForNorma->hash_id], false),
                    ], false),
                ],
                [
                    'stream_id' => $resForNorma2->place_id,
                    'answer' => $resForNorma2->answer,
                    'last_answered_at' => $resForNorma2->answered_at?->toDateString(),
                    'next_due_at' => $resForNorma2->next_due_at?->toDateString(),
                    'frequency' => $resForNorma2->frequency,
                    'frequency_interval' => $resForNorma2->frequency_interval->value,
                    'link' => route('my.normas.activate.redirect', [
                        'norma' => $resForNorma2->place_id,
                        'redirect' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $resForNorma2->hash_id], false),
                    ], false),
                ],
            ],
        ];
        $responseOtherNorma = [
            'id' => $forOtherNorma->id,
            'description' => $forOtherNorma->toDescription(),
            'risk_rating' => $forOtherNorma->risk_rating,
            'responses' => [
                [
                    'stream_id' => $resForOtherNorma->place_id,
                    'answer' => $resForOtherNorma->answer,
                    'last_answered_at' => $resForOtherNorma->answered_at?->toDateString(),
                    'next_due_at' => $resForOtherNorma->next_due_at?->toDateString(),
                    'frequency' => $resForOtherNorma->frequency,
                    'frequency_interval' => $resForOtherNorma->frequency_interval->value,
                    'link' => route('my.normas.activate.redirect', [
                        'norma' => $resForOtherNorma->place_id,
                        'redirect' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $resForOtherNorma->hash_id], false),
                    ], false),
                ],
            ],
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01']))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseNorma,
                    $responseOtherNorma,
                ],
                'links' => [
                    'first' => route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01', 'page' => 1]),
                    'last' => route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01', 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.assessment-items.index', ['organisation' => $organisation->id]),
                    'per_page' => 50,
                    'to' => 2,
                    'total' => 2,
                ],
            ]);

        $streams = $norma->id;

        $route = route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    [
                        ...$responseNorma,
                        'responses' => [
                            [
                                ...$responseNorma['responses'][0],
                            ],
                        ],
                    ],
                ],
                'links' => [
                    'first' => route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.assessment-items.index', ['organisation' => $organisation->id]),
                    'per_page' => 50,
                    'to' => 1,
                    'total' => 1,
                ],
            ]);

        $route = route('api.v3.assessment-items.show', [
            'organisation' => $organisation->id,
            'assessment_item' => $forOtherNorma->id,
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherNorma,
            ]);
    }
}
