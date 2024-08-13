<?php

namespace Tests\Feature\Api\V3\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use Tests\Feature\Api\V3\ApiV3TestCase;

class AssessmentItemControllerTest extends ApiV3TestCase
{
    public function testAssessmentItemsForOrganisation(): void
    {
        /** @var Libryo $libryo */
        [$libryo, $organisation] = $this->initCompiledStream();

        $otherLibryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);
        $forLibryo = AssessmentItem::factory()->create();
        $forOtherLibryo = AssessmentItem::factory()->create();

        $resForLibryo = AssessmentItemResponse::factory()->create(['assessment_item_id' => $forLibryo->id, 'place_id' => $libryo->id])->refresh();
        $resForLibryo2 = AssessmentItemResponse::factory()->create(['assessment_item_id' => $forLibryo->id, 'place_id' => $otherLibryo->id])->refresh();
        $resForOtherLibryo = AssessmentItemResponse::factory()->create(['assessment_item_id' => $forOtherLibryo->id, 'place_id' => $otherLibryo->id])->refresh();

        $responseLibryo = [
            'id' => $forLibryo->id,
            'description' => $forLibryo->toDescription(),
            'risk_rating' => $forLibryo->risk_rating,
            'responses' => [
                [
                    'stream_id' => $resForLibryo->place_id,
                    'answer' => $resForLibryo->answer,
                    'last_answered_at' => $resForLibryo->answered_at?->toDateString(),
                    'next_due_at' => $resForLibryo->next_due_at?->toDateString(),
                    'frequency' => $resForLibryo->frequency,
                    'frequency_interval' => $resForLibryo->frequency_interval->value,
                    'link' => route('my.libryos.activate.redirect', [
                        'libryo' => $resForLibryo->place_id,
                        'redirect' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $resForLibryo->hash_id], false),
                    ], false),
                ],
                [
                    'stream_id' => $resForLibryo2->place_id,
                    'answer' => $resForLibryo2->answer,
                    'last_answered_at' => $resForLibryo2->answered_at?->toDateString(),
                    'next_due_at' => $resForLibryo2->next_due_at?->toDateString(),
                    'frequency' => $resForLibryo2->frequency,
                    'frequency_interval' => $resForLibryo2->frequency_interval->value,
                    'link' => route('my.libryos.activate.redirect', [
                        'libryo' => $resForLibryo2->place_id,
                        'redirect' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $resForLibryo2->hash_id], false),
                    ], false),
                ],
            ],
        ];
        $responseOtherLibryo = [
            'id' => $forOtherLibryo->id,
            'description' => $forOtherLibryo->toDescription(),
            'risk_rating' => $forOtherLibryo->risk_rating,
            'responses' => [
                [
                    'stream_id' => $resForOtherLibryo->place_id,
                    'answer' => $resForOtherLibryo->answer,
                    'last_answered_at' => $resForOtherLibryo->answered_at?->toDateString(),
                    'next_due_at' => $resForOtherLibryo->next_due_at?->toDateString(),
                    'frequency' => $resForOtherLibryo->frequency,
                    'frequency_interval' => $resForOtherLibryo->frequency_interval->value,
                    'link' => route('my.libryos.activate.redirect', [
                        'libryo' => $resForOtherLibryo->place_id,
                        'redirect' => route('my.assess.assessment-item-responses.show', ['aiResponse' => $resForOtherLibryo->hash_id], false),
                    ], false),
                ],
            ],
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01']))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseLibryo,
                    $responseOtherLibryo,
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

        $streams = $libryo->id;

        $route = route('api.v3.assessment-items.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    [
                        ...$responseLibryo,
                        'responses' => [
                            [
                                ...$responseLibryo['responses'][0],
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
            'assessment_item' => $forOtherLibryo->id,
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherLibryo,
            ]);
    }
}
