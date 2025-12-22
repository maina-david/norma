<?php

namespace Tests\Feature\Api\V1\Corpus;

use App\Enums\Corpus\ReferenceType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use App\Models\Ontology\Tag;
use App\Models\Requirements\Consequence;
use App\Models\Requirements\Summary;
use Tests\Feature\Api\ApiTestCase;

class ReferenceControllerTest extends ApiTestCase
{
    private function getItemData(Reference $reference, Tag $tag): array
    {
        return [
            'id' => (int) $reference->id,
            'citation_type' => null,
            'number' => $reference->refPlainText?->plain_text ? '' : ($reference->citation->number ?? ''),
            'register_id' => $reference->work_id,
            'version' => null,
            'status' => 1,
            'title' => $reference->refPlainText?->plain_text,
            'derived' => true,
            'sub_line' => '',
            'content' => $reference->htmlContent?->cached_content,
            'position' => $reference->position ?? ($reference->citation->position ?? ''),
            'item_type' => null,
            'summary_id' => $reference->id,
            'level' => $reference->level,
            'is_toc_item' => true,
            'visible_in_toc' => true,
            'type' => $reference->citation->type ?? '',
            'is_section' => (bool) ($reference->is_section ?? ($reference->citation->is_section ?? false)),
            'author_id' => null,
            'tags' => [
                'data' => [
                    [
                        'id' => $tag->id,
                        'title' => $tag->title,
                        'tag_type_id' => $tag->tag_type_id,
                        'created_at' => $tag->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $tag->updated_at->format('Y-m-d H:i:s'),
                    ],
                ],
            ],
            'created_at' => $reference->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $reference->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function testForNormaForWork(): void
    {
        [$norma, $organisation, $work, $requirementsLocation, $domain, $tag] = $this->initCompiledStream();
        $user = User::factory()->create();
        $user->normas()->attach($norma);

        $routeName = 'api.v1.legislation.sections.for.work';
        $route = route($routeName, ['work' => $work, 'norma' => $norma]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];
        $reference = $work->references->load(['citation', 'htmlContent', 'refPlainText'])->first();

        $items[] = $this->getItemData($reference, $tag);
        $pagination = [
            'total' => $work->references->count(),
            'page' => 'all',
            'perPage' => 'all',
        ];

        $response->assertJson([
            'data' => $items,
            'meta' => [
                'pagination' => $pagination,
            ],
        ], true);

        $summary = Summary::factory()->create(['reference_id' => $reference->id]);

        $summaryData = ['data' => [
            [
                'summary' => [
                    'data' => [
                        'id' => $summary->reference_id,
                        'summary_body' => $summary->summary_body,
                        'created_at' => $summary->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $summary->updated_at->format('Y-m-d H:i:s'),
                    ],
                ],
            ],
        ]];
        $response->assertJsonMissing($summaryData);

        $response = $this->json('get', $route)->assertSuccessful();
        $response->assertJson($summaryData);

        $this->deleteCompiledStream();
    }

    public function testShow(): void
    {
        $reference = Reference::factory()->create();
        $reference->htmlContent()->save(new ReferenceContent(['reference_id' => $reference->id, 'cached_content' => '<p>Test</p>']));
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $reference->tags()->attach($tag);
        $consequenceGroup = Reference::factory()->for($reference->work)->create([
            'type' => ReferenceType::consequenceGroup()->value,
        ]);
        $consequence = Consequence::factory()->create();
        $reference->raisesConsequenceGroups()->attach($consequenceGroup, [
            'link_type' => 3,
            'parent_work_id' => $reference->work->id,
            'child_work_id' => $reference->work->id,
        ]);
        $consequenceGroup->consequences()->attach($consequence);

        $data = $this->getItemData($reference, $tag);
        $reference->refresh();
        $data['created_at'] = $reference->created_at->format('Y-m-d H:i:s');
        $data['updated_at'] = $reference->updated_at->format('Y-m-d H:i:s');

        $routeName = 'api.v1.legislation.items.show';
        $route = route($routeName, ['id' => $reference->id, 'include' => 'tags,raisesConsequenceGroups,raisesConsequenceGroups.consequences']);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $data['raisesConsequenceGroups'] = [
            'data' => [
                [
                    'id' => $consequenceGroup->id,
                    'consequences' => [
                        'data' => [
                            [
                                'id' => $consequence->id,
                                'consequence_type' => (int) $consequence->consequence_type,
                                'consequence_other_detail' => $consequence->consequence_other_detail,
                                'penalty' => false,
                                'penalty_type' => 0,
                                'penalty_other_details' => null,
                                'personal_liability' => false,
                                'amount' => $consequence->amount ? (int) $consequence->amount : null,
                                'currency' => $consequence->currency,
                                'sentence_period' => $consequence->sentence_period ? (int) $consequence->sentence_period : null,
                                'sentence_period_type' => $consequence->sentence_period_type ? (int) $consequence->sentence_period_type : null,
                                'per_day' => (bool) $consequence->per_day,
                                'activated' => false,
                                'description' => $consequence->toReadableString(),
                                'severity' => $consequence->severity ? (int) $consequence->severity : null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response->assertJson(['data' => $data]);
    }
}
