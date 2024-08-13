<?php

namespace Tests\Feature\Api\V3\Corpus;

use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Services\Html\HtmlToText;
use Tests\Feature\Api\V3\ApiV3TestCase;

class RequirementControllerTest extends ApiV3TestCase
{
    public function testRequirementsForOrganisation(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag, $childWork] = $this->initCompiledStream();

        $noWorkLibryo = Libryo::factory()->create(['organisation_id' => $organisation->id]);

        $references = Reference::whereRelation('libryos.organisation', 'id', $organisation->id)
            ->with([
                'htmlContent',
                'refPlainText',
                'libryos' => fn ($q) => $q->where('organisation_id', $organisation->id),
            ])
            ->get()
            ->map(function ($ref) {
                $ref->htmlContent()->create();

                return [
                    'id' => $ref->id,
                    'title' => $ref->refPlainText->plain_text ?? '',
                    'consequences' => [],
                    'content' => trim(app(HtmlToText::class)->convert($ref->htmlContent->cached_content ?? '')),
                    'work_id' => $ref->work_id,
                    'streams' => $ref->libryos->pluck('id')->all(),
                    'link' => route('my.corpus.references.show', ['reference' => $ref->id], false),
                ];
            })
            ->all();

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.requirements.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01']))
            ->assertJsonCount(6, 'data')
            ->assertExactJson([
                'data' => $references,
                'links' => [
                    'first' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01', 'page' => 1]),
                    'last' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01', 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.requirements.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => 6,
                    'total' => 6,
                ],
            ]);

        $workReferences = Reference::whereRelation('libryos.organisation', 'id', $organisation->id)
            ->where('work_id', $work->id)
            ->with([
                'htmlContent',
                'refPlainText',
                'libryos' => fn ($q) => $q->where('organisation_id', $organisation->id),
            ])
            ->get()
            ->map(fn ($ref) => [
                'id' => $ref->id,
                'title' => $ref->refPlainText->plain_text ?? '',
                'content' => trim(app(HtmlToText::class)->convert($ref->htmlContent->cached_content ?? '')),
                'consequences' => [],
                'work_id' => $ref->work_id,
                'streams' => $ref->libryos->pluck('id')->all(),
                'link' => route('my.corpus.references.show', ['reference' => $ref->id], false),
            ])
            ->all();

        $route = route('api.v3.requirements.index', ['organisation' => $organisation->id, 'works' => $work->id]);

        $this->getJson($route)
            ->assertJsonCount(3, 'data')
            ->assertExactJson([
                'data' => $workReferences,
                'links' => [
                    'first' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'works' => $work->id, 'page' => 1]),
                    'last' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'works' => $work->id, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.requirements.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => 3,
                    'total' => 3,
                ],
            ]);

        $streams = implode(',', [$libryo->id, $noWorkLibryo->id]);

        $route = route('api.v3.requirements.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(6, 'data')
            ->assertExactJson([
                'data' => $references,
                'links' => [
                    'first' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.requirements.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => 6,
                    'total' => 6,
                ],
            ]);

        $streams = implode(',', [$noWorkLibryo->id]);

        $route = route('api.v3.requirements.index', ['organisation' => $organisation->id, 'streams' => $streams]);

        $this->getJson($route)
            ->assertJsonCount(0, 'data')
            ->assertExactJson([
                'data' => [],
                'links' => [
                    'first' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.requirements.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'path' => route('api.v3.requirements.index', ['organisation' => $organisation->id]),
                    'per_page' => 100,
                    'to' => null,
                    'total' => 0,
                ],
            ]);

        $route = route('api.v3.requirements.show', [
            'organisation' => $organisation->id,
            'requirement' => $references[0]['id'],
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => $references[0],
            ]);
    }
}
