<?php

namespace Tests\Feature\Api\V3\Notify;

use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use App\Services\Html\HtmlToText;
use Tests\Feature\Api\V3\ApiV3TestCase;

class LegalUpdateControllerTest extends ApiV3TestCase
{
    public function testUpdatesForOrganisation(): void
    {
        /** @var Norma $norma */
        [$norma, $organisation] = $this->initCompiledStream();

        $otherNorma = Norma::factory()->create(['organisation_id' => $organisation->id]);
        $forNorma = LegalUpdate::factory()->create();
        $forOtherNorma = LegalUpdate::factory()->create();
        $norma->legalUpdates()->attach($forNorma);
        $otherNorma->legalUpdates()->attach($forNorma);
        $otherNorma->legalUpdates()->attach($forOtherNorma);

        $responseNorma = [
            'id' => $forNorma->id,
            'title' => $forNorma->title,
            'publication_number' => $forNorma->publication_number,
            'publication_document_number' => $forNorma->publication_document_number,
            'publication_date' => $forNorma->publication_date?->toDateString(),
            'effective_date' => $forNorma->effective_date?->toDateString(),
            'highlights' => trim(app(HtmlToText::class)->convert($forNorma->highlights ?? '')),
            'streams' => [$norma->id, $otherNorma->id],
            'link' => route('my.notify.legal-updates.show', ['update' => $forNorma->id], false),
        ];
        $responseOtherNorma = [
            'id' => $forOtherNorma->id,
            'title' => $forOtherNorma->title,
            'publication_number' => $forOtherNorma->publication_number,
            'publication_document_number' => $forOtherNorma->publication_document_number,
            'publication_date' => $forOtherNorma->publication_date?->toDateString(),
            'effective_date' => $forOtherNorma->effective_date?->toDateString(),
            'highlights' => trim(app(HtmlToText::class)->convert($forOtherNorma->highlights ?? '')),
            'streams' => [$otherNorma->id],
            'link' => route('my.notify.legal-updates.show', ['update' => $forOtherNorma->id], false),
        ];

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.updates.index', ['organisation' => $organisation->id]))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    $responseNorma,
                    $responseOtherNorma,
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

        $streams = $norma->id;

        $route = route('api.v3.updates.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(1, 'data')
            ->assertExactJson([
                'data' => [
                    [
                        ...$responseNorma,
                        'streams' => [$norma->id],
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
            'update' => $forOtherNorma->id,
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $responseOtherNorma,
            ]);
    }
}
