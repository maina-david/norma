<?php

namespace Tests\Feature\Api\V2\Requirements;

use App\Models\Auth\User;
use App\Models\Geonames\Location;
use Tests\Feature\Api\ApiTestCase;

class LegalReportControllerTest extends ApiTestCase
{
    public function testLegalReport(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $country = Location::factory()->create();
        $requirementsCollection->update(['location_country_id' => $country->id]);
        $user = User::factory()->create();

        $user->libryos()->attach($libryo);

        $routeName = 'api.v2.legislation.report';
        $route = route($routeName, ['libryo' => $libryo]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];

        $work->load(['references.citation', 'references.refPlainText']);
        $reference = $work->references->first();
        $reference2 = $work->references[1];
        $lastReference = $work->references->last();
        $libryo->references()->detach($lastReference);

        $childWork = $work->children->first();
        $items[] = [
            'id' => $work->id,
            'iri' => $work->iri,
            'work_number' => $work->work_number,
            'title' => $work->title,
            'status' => $work->status,
            'has_xml' => (bool) $work->has_xml,
            'work_type' => $work->getTypeName(),
            'sub_type' => $work->sub_type,
            'language_code' => $work->language_code,
            'enacted_date' => $work->creation_date,
            'publication_date' => $work->publication_date,
            'primary_location_id' => $work->primary_location_id,
            'title_translation' => $work->title_translation,
            'short_title' => $work->short_title,
            'highlights' => $work->highlights,
            'update_report' => $work->update_report,
            'issuing_authority' => $work->issuing_authority,
            'effective_date' => $work->effective_date?->format('Y-m-d') ?? null,
            'comment_date' => $work->comment_date?->format('Y-m-d') ?? null,
            'gazette_number' => $work->gazette_number,
            'notice_number' => $work->notice_number,
            'citations' => [
                [
                    'id' => $reference->id,
                    'work_id' => $work->id,
                    'number' => $reference->refPlainText?->plain_text ? '' : ($reference->citation->number ?? ''),
                    'title' => $reference->refPlainText?->plain_text,
                ],
                [
                    'id' => $reference2->id,
                    'work_id' => $work->id,
                    'number' => $reference2->refPlainText?->plain_text ? '' : ($reference2->citation->number ?? ''),
                    'title' => $reference2->refPlainText?->plain_text,
                ],
            ],
            'locations' => [
                [
                    'id' => $requirementsCollection->id,
                    'title' => $requirementsCollection->title,
                    'slug' => $requirementsCollection->slug,
                    'location_type_id' => $requirementsCollection->location_type_id,
                    'flag' => $requirementsCollection->flag,
                    'parent_id' => $requirementsCollection->parent_id,
                    'level' => $requirementsCollection->level,
                    'location_country_id' => $requirementsCollection->location_country_id,
                    'location_code' => $requirementsCollection->location_code,
                    'country' => [
                        'id' => $requirementsCollection->country->id,
                        'title' => $requirementsCollection->country->title,
                        'location_type_id' => $requirementsCollection->country->location_type_id,
                        'flag' => $requirementsCollection->country->flag,
                        'slug' => $requirementsCollection->country->slug,
                        'parent_id' => $requirementsCollection->country->parent_id,
                    ],
                    'locationType' => [
                        'id' => $requirementsCollection->type->id,
                        'title' => $requirementsCollection->type->title,
                        'slug' => $requirementsCollection->type->slug,
                    ],
                ],
            ],
            'children' => [
                [
                    'id' => $childWork->id,
                    'title' => $childWork->title,
                    'citations' => [
                        [
                            'id' => $childWork->references->first()->id,
                        ],
                        [
                            'id' => $childWork->references[1]->id,
                        ],
                    ],
                    'locations' => [
                        [
                            'id' => $requirementsCollection->id,
                            'country' => [
                                'id' => $requirementsCollection->country->id,
                            ],
                            'locationType' => [
                                'id' => $requirementsCollection->type->id,
                            ],
                        ],
                    ],
                    'legalDomains' => [
                        [
                            'id' => $domain->id,
                            'title' => $domain->title,
                        ],
                    ],
                ],
            ],
            'legalDomains' => [
                [
                    'id' => $domain->id,
                    'title' => $domain->title,
                ],
            ],
        ];

        $response->assertJson([
            'data' => $items,
        ], true);

        // we removed $lastReference from the compilation, so shouldn't show
        $response->assertJsonMissing(['data' => [
            [
                'citations' => [
                    [
                        'id' => $lastReference->id,
                    ],
                ],
            ],
        ]]);
        $response->assertJson(['data' => [
            [
                'citations' => [
                    [
                        'id' => $reference->id,
                    ],
                ],
            ],
        ]]);

        // test with pagination
        $pagination = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 1,
            'path' => route($routeName, ['libryo' => $libryo]),
            'per_page' => 1,
            'to' => 1,
            'total' => 1,
        ];
        $links = [
            'first' => route($routeName, ['libryo' => $libryo, 'perPage' => 1, 'page' => 1]),
            'last' => route($routeName, ['libryo' => $libryo, 'perPage' => 1, 'page' => 1]),
            'prev' => null,
            'next' => null,
        ];
        $route = route($routeName, ['libryo' => $libryo, 'perPage' => 1, 'page' => 1]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[0]], 'meta' => $pagination, 'links' => $links]);
        $this->deleteCompiledStream();
    }
}
