<?php

namespace Tests\Feature\Api\V2\Assess;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use Tests\Feature\Api\ApiTestCase;

class AssessmentItemControllerTest extends ApiTestCase
{
    public function testIndexForLibryo(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $aItem = AssessmentItem::factory()->create(['legal_domain_id' => $domain->id]);
        $aItemResponse = AssessmentItemResponse::factory()->for($aItem)->for($libryo)->create();
        $reference = $work->references->load(['citation', 'htmlContent', 'refPlainText'])->first();
        $aItem->references()->attach($reference);

        $user = User::factory()->create();
        $user->libryos()->attach($libryo);

        $routeName = 'api.v2.assessment-items.for.libryo';
        $route = route($routeName, ['libryo' => $libryo, 'include' => 'basicCitations|basicCitations.work|basicCitations.work.locations|guidanceNotes|legalDomain']); // these are the includes that Cleanchain still uses
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];

        $items[] = [
            'id' => $aItem->id,
            'description' => $aItem->toDescription(),
            'frequency' => $aItem->frequency,
            'risk_rating' => $aItem->risk_rating,
            'updated_at' => $aItem->created_at->format('Y-m-d H:i:s'),
            'basic_citations' => [
                [
                    'id' => $reference->id,
                    'citation_type' => null, // legacy
                    'number' => $reference->refPlainText?->plain_text ? null : ($reference->citation->number ?? null),
                    'register_id' => $reference->work_id,
                    'title' => $reference->refPlainText?->plain_text ?: null,
                    'sub_line' => null, // legacy
                    'derived' => false,
                    'position' => $reference->start,
                    'work' => [
                        'id' => $reference->work_id,
                        // ....
                    ],
                    'is_toc_item' => true,
                    'is_section' => $reference->is_section ?? ($reference->citation->is_section ?? false),
                    'level' => $reference->level,
                    'volume' => $reference->volume,
                    'type' => $reference->type,
                    'content' => $reference->htmlContent?->cached_content,
                ],
            ],
            'legal_domain' => [
                'id' => $domain->id,
                'title' => $domain->title,
            ],
        ];

        $response->assertJson([
            'data' => $items,
        ], true);

        $this->deleteCompiledStream();
    }

    public function testShowForLibryo(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $aItem = AssessmentItem::factory()->create(['legal_domain_id' => $domain->id]);
        $aItemResponse = AssessmentItemResponse::factory()->for($aItem)->for($libryo)->create();
        $reference = $work->references->load(['citation', 'htmlContent', 'refPlainText'])->first();
        $aItem->references()->attach($reference);

        $user = User::factory()->create();
        $user->libryos()->attach($libryo);

        $routeName = 'api.v2.assessment-items.for.libryo.show';
        $route = route($routeName, ['assessmentItem' => $aItem, 'libryo' => $libryo, 'include' => 'basicCitations|basicCitations.work|legalDomain']); // these are the includes that Cleanchain still uses
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $item = [
            'id' => $aItem->id,
            'description' => $aItem->toDescription(),
            'frequency' => $aItem->frequency,
            'risk_rating' => $aItem->risk_rating,
            'updated_at' => $aItem->created_at->format('Y-m-d H:i:s'),
            'basic_citations' => [
                [
                    'id' => $reference->id,
                    'citation_type' => null, // legacy
                    'number' => $reference->refPlainText?->plain_text ? null : ($reference->citation->number ?? null),
                    'register_id' => $reference->work_id,
                    'title' => $reference->refPlainText?->plain_text ?: null,
                    'sub_line' => null, // legacy
                    'derived' => false,
                    'position' => $reference->start,
                    'work' => [
                        'id' => $reference->work_id,
                        // ....
                    ],
                    'is_toc_item' => true,
                    'is_section' => $reference->is_section ?? ($reference->citation->is_section ?? false),
                    'level' => $reference->level,
                    'volume' => $reference->volume,
                    'type' => $reference->type,
                    'content' => $reference->htmlContent?->cached_content,
                ],
            ],
            'legal_domain' => [
                'id' => $domain->id,
                'title' => $domain->title,
            ],
        ];

        $response->assertJson([
            'data' => $item,
        ], true);

        $this->deleteCompiledStream();
    }
}
