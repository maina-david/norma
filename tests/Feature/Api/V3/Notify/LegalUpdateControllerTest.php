<?php

namespace Tests\Feature\Api\V3\Notify;

use App\Models\Customer\Libryo;
use App\Models\Notify\LegalUpdate;
use App\Services\Html\HtmlToText;
use Tests\Feature\Api\V3\ApiV3TestCase;

class LegalUpdateControllerTest extends ApiV3TestCase
{
    public function testUpdatesForOrganisation(): void
    {
        /** @var Libryo $libryo */
        [$libryo, $organisation] = $this->initCompiledStream();

        $otherLibryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);
        $forLibryo = LegalUpdate::factory()->create();
        $forOtherLibryo = LegalUpdate::factory()->create();
        $libryo->legalUpdates()->attach($forLibryo);
        $otherLibryo->legalUpdates()->attach($forLibryo);
        $otherLibryo->legalUpdates()->attach($forOtherLibryo);

        $responseLibryo = [
            'id' => $forLibryo->id,
            'title' => $forLibryo->title,
            'publication_number' => $forLibryo->publication_number,
            'publication_document_number' => $forLibryo->publication_document_number,
            'publication_date' => $forLibryo->publication_date?->toDateString(),
            'effective_date' => $forLibryo->effective_date?->toDateString(),
            'highlights' => trim(app(HtmlToText::class)->convert($forLibryo->highlights ?? '')),
            'streams' => [$libryo->id, $otherLibryo->id],
            'link' => route('my.notify.legal-updates.show', ['update' => $forLibryo->id], false),
        ];
        $responseOtherLibryo = [
            'id' => $forOtherLibryo->id,
            'title' => $forOtherLibryo->title,
            'publication_number' => $forOtherLibryo->publication_number,
            'publication_document_number' => $forOtherLibryo->publication_document_number,
            'publication_date' => $forOtherLibryo->publication_date?->toDateString(),
            'effective_date' => $forOtherLibryo->effective_date?->toDateString(),
            'highlights' => trim(app(HtmlToText::class)->convert($forOtherLibryo->highlights ?? '')),
            'streams' => [$otherLibryo->id],
            'link' => route('my.notify.legal-updates.show', ['update' => $forOtherLibryo->id], false),
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.updates.index', ['organisation' => $organisation->id]))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseLibryo,
                    $responseOtherLibryo,
                ],
                'links' => [
                    'first' => route('api.v3.updates.index', ['organisation' => $organisation->id, 'page' => 1]),
                    'last' => route('api.v3.updates.index', ['organisation' => $organisation->id, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.updates.index', ['organisation' => $organisation->id]),
                    'per_page' => 500,
                    'to' => 2,
                    'total' => 2,
                ],
            ]);

        $streams = $libryo->id;

        $route = route('api.v3.updates.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    [
                        ...$responseLibryo,
                        'streams' => [$libryo->id],
                    ],
                ],
                'links' => [
                    'first' => route('api.v3.updates.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.updates.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.updates.index', ['organisation' => $organisation->id]),
                    'per_page' => 500,
                    'to' => 1,
                    'total' => 1,
                ],
            ]);

        $route = route('api.v3.updates.show', [
            'organisation' => $organisation->id,
            'update' => $forOtherLibryo->id,
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherLibryo,
            ]);
    }
}
