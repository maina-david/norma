<?php

namespace Tests\Feature\Api\V1\Requirements;

use App\Models\Auth\User;
use App\Models\Geonames\Location;
use Carbon\Carbon;
use Tests\Feature\Api\ApiTestCase;

class LegalReportControllerTest extends ApiTestCase
{
    public function testLegalReport(): void
    {
        $this->freezeTime();

        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $country = Location::factory()->create();
        $requirementsCollection->update(['location_country_id' => $country->id]);
        $user = User::factory()->create();

        $user->normas()->attach($norma);

        $items = [];

        $work->load(['references.citation', 'references.refPlainText']);
        $reference = $work->references->first();
        $reference2 = $work->references[1];
        $lastReference = $work->references->last();
        $norma->references()->detach($lastReference);

        $childWork = $work->children->first();
        $items[] = [
            'id' => $work->id,
            'title' => $work->title,
            'status' => $work->status,
            'register_type' => $work->getTypeName(),
            'source_text' => $work->source_url ?? '',
            'url' => $work->source_url ?? '',
            'source_id' => $work->source_id,
            'language_code' => $work->language_code,
            'work_number' => $work->work_number,
            'iri' => $work->iri,
            'sub_type' => $work->sub_type,
            'creation_date' => $work->work_date instanceof Carbon ? $work->work_date->toISOString() : $work->work_date,
            'publication_date' => $work->effective_date?->format('Y-m-d') ?? null,
            'created_at' => $work->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $work->updated_at->format('Y-m-d H:i:s'),
            'registerItems' => [
                'data' => [
                    [
                        'id' => $reference->id,
                        'number' => $reference->refPlainText?->plain_text ? '' : ($reference->citation->number ?? ''),
                        'register_id' => $work->id,
                        'title' => $reference->refPlainText?->plain_text,
                        'position' => $reference->position ?? ($reference->citation->position ?? ''),
                        'created_at' => $reference->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $reference->updated_at->format('Y-m-d H:i:s'),
                    ],
                    [
                        'id' => $reference2->id,
                        'number' => $reference2->refPlainText?->plain_text ? '' : ($reference2->citation->number ?? ''),
                        'register_id' => $work->id,
                        'title' => $reference2->refPlainText?->plain_text,
                        'position' => $reference2->position ?? ($reference2->citation->position ?? ''),
                        'created_at' => $reference2->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $reference2->updated_at->format('Y-m-d H:i:s'),
                    ],
                ],
            ],
            'locations' => [
                'data' => [
                    [
                        'id' => $requirementsCollection->id,
                        'title' => $requirementsCollection->title,
                        'location_type_id' => $requirementsCollection->location_type_id,
                        'flag' => $requirementsCollection->flag,
                        'parent_id' => $requirementsCollection->parent_id,
                        'level' => $requirementsCollection->level,
                        'location_country_id' => $requirementsCollection->location_country_id,
                        'location_code' => $requirementsCollection->location_code,
                        'country' => [
                            'data' => [
                                'id' => $requirementsCollection->country->id,
                                'title' => $requirementsCollection->country->title,
                                'location_type_id' => $requirementsCollection->country->location_type_id,
                                'flag' => $requirementsCollection->country->flag,
                            ],
                        ],
                        'locationType' => [
                            'data' => [
                                'id' => $requirementsCollection->type->id,
                                'title' => $requirementsCollection->type->title,
                                'slug' => $requirementsCollection->type->slug,
                                'adjective_key' => $requirementsCollection->type->adjective_key,
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                'data' => [
                    [
                        'id' => $childWork->id,
                        'title' => $childWork->title,
                        'registerItems' => [
                            'data' => [
                                [
                                    'id' => $childWork->references->first()->id,
                                ],
                                [
                                    'id' => $childWork->references[1]->id,
                                ],
                            ],
                        ],
                        'locations' => [
                            'data' => [
                                [
                                    'id' => $requirementsCollection->id,
                                    'country' => [
                                        'data' => [
                                            'id' => $requirementsCollection->country->id,
                                        ],
                                    ],
                                    'locationType' => [
                                        'data' => [
                                            'id' => $requirementsCollection->type->id,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'legalDomains' => [
                            'data' => [
                                [
                                    'id' => $domain->id,
                                    'title' => $domain->title,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'legalDomains' => [
                'data' => [
                    [
                        'id' => $domain->id,
                        'title' => $domain->title,
                    ],
                ],
            ],
        ];

        // make sure legacy v1 response stays the same
        $pagination = [
            'total' => 1,
            'page' => 'all',
            'perPage' => 'all',
        ];

        $routeName = 'api.v1.legislation.legal-report';
        $route = route($routeName, ['norma' => $norma]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $response->assertJson([
            'data' => $items,
            'meta' => [
                'pagination' => $pagination,
            ],
        ], true);

        // we removed $lastReference from the compilation, so shouldn't show
        $response->assertJsonMissing(['data' => [
            [
                'registerItems' => [
                    'data' => [
                        [
                            'id' => $lastReference->id,
                        ],
                    ],
                ],
            ],
        ]]);
        $response->assertJson(['data' => [
            [
                'registerItems' => [
                    'data' => [
                        [
                            'id' => $reference->id,
                        ],
                    ],
                ],
            ],
        ]]);

        // test with pagination
        $pagination = [
            'total' => 1,
            'page' => 1,
            'perPage' => 1,
        ];
        $route = route($routeName, ['norma' => $norma, 'perPage' => 1, 'page' => 1]);
        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson(['data' => [$items[0]], 'meta' => ['pagination' => $pagination]]);
        $this->deleteCompiledStream();
    }
}
