<?php

namespace Tests\Feature\Api\V3\Corpus;

use App\Models\Customer\Norma;
use Tests\Feature\Api\V3\ApiV3TestCase;

class WorkControllerTest extends ApiV3TestCase
{
    public function testWorksForOrganisation(): void
    {
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag, $childWork] = $this->initCompiledStream();

        $noWorkNorma = Norma::factory()->create(['organisation_id' => $organisation->id]);

        $this->assertUnauthorizedThenRun($organisation, 'get', route('api.v3.works.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01']))
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    [
                        'id' => $work->id,
                        'title' => $work->title,
                        'work_type' => $work->work_type,
                        'language_code' => $work->language_code,
                        'publication_date' => $work->work_date,
                        'effective_date' => $work->effective_date,
                        'link' => route('my.corpus.works.show', ['work' => $work->id], false),
                        'children' => [$childWork->id],
                    ],
                    [
                        'id' => $childWork->id,
                        'title' => $childWork->title,
                        'work_type' => $childWork->work_type,
                        'language_code' => $childWork->language_code,
                        'publication_date' => $childWork->work_date,
                        'effective_date' => $childWork->effective_date,
                        'link' => route('my.corpus.works.show', ['work' => $childWork->id], false),
                        'children' => [],
                    ],
                ],
                'links' => [
                    'first' => route('api.v3.works.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01', 'page' => 1]),
                    'last' => route('api.v3.works.index', ['organisation' => $organisation->id, 'updatedAfter' => '2023-01-01', 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.works.index', ['organisation' => $organisation->id]),
                    'per_page' => 500,
                    'to' => 2,
                    'total' => 2,
                ],
            ]);

        $streams = implode(',', [$norma->id, $noWorkNorma->id]);

        $route = route('api.v3.works.index', [
            'organisation' => $organisation->id,
            'streams' => $streams,
        ]);

        $this->getJson($route)
            ->assertJsonCount(2, 'data')
            ->assertExactJson([
                'data' => [
                    [
                        'id' => $work->id,
                        'title' => $work->title,
                        'work_type' => $work->work_type,
                        'language_code' => $work->language_code,
                        'publication_date' => $work->work_date,
                        'effective_date' => $work->effective_date,
                        'link' => route('my.corpus.works.show', ['work' => $work->id], false),
                        'children' => [$childWork->id],
                    ],
                    [
                        'id' => $childWork->id,
                        'title' => $childWork->title,
                        'work_type' => $childWork->work_type,
                        'language_code' => $childWork->language_code,
                        'publication_date' => $childWork->work_date,
                        'effective_date' => $childWork->effective_date,
                        'link' => route('my.corpus.works.show', ['work' => $childWork->id], false),
                        'children' => [],
                    ],
                ],
                'links' => [
                    'first' => route('api.v3.works.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.works.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'path' => route('api.v3.works.index', ['organisation' => $organisation->id]),
                    'per_page' => 500,
                    'to' => 2,
                    'total' => 2,
                ],
            ]);

        $streams = implode(',', [$noWorkNorma->id]);

        $route = route('api.v3.works.index', [
            'organisation' => $organisation->id,
            'streams' => $streams,
        ]);

        $this->getJson($route)
            ->assertJsonCount(0, 'data')
            ->assertExactJson([
                'data' => [],
                'links' => [
                    'first' => route('api.v3.works.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'last' => route('api.v3.works.index', ['organisation' => $organisation->id, 'streams' => $streams, 'page' => 1]),
                    'prev' => null,
                    'next' => null,
                ],
                'meta' => [
                    'current_page' => 1,
                    'from' => null,
                    'last_page' => 1,
                    'path' => route('api.v3.works.index', ['organisation' => $organisation->id]),
                    'per_page' => 500,
                    'to' => null,
                    'total' => 0,
                ],
            ]);

        $route = route('api.v3.works.show', [
            'organisation' => $organisation->id,
            'work' => $work->id,
        ]);

        $this->getJson($route)
            ->assertExactJson([
                'data' => [
                    'id' => $work->id,
                    'title' => $work->title,
                    'work_type' => $work->work_type,
                    'language_code' => $work->language_code,
                    'publication_date' => $work->work_date,
                    'effective_date' => $work->effective_date,
                    'link' => route('my.corpus.works.show', ['work' => $work->id], false),
                    'children' => [$childWork->id],
                ],
            ]);
    }
}
