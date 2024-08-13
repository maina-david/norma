<?php

namespace Tests\Feature\Api\V1\Corpus;

use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Carbon\Carbon;
use Tests\Feature\Api\ApiTestCase;

class WorkControllerTest extends ApiTestCase
{
    private function getItemData(Work $work): array
    {
        $work->refresh()->load(['references.citation', 'references.refPlainText']);
        $reference = $work->references->first();
        $reference2 = $work->references[1];

        return [
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
                        'updated_at' => $reference->created_at->format('Y-m-d H:i:s'),
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
        ];
    }

    public function testShow(): void
    {
        $work = Work::factory()->has(Reference::factory()->count(5))->create();
        $user = User::factory()->create();

        $routeName = 'api.v1.registers.show';
        $route = route($routeName, ['id' => $work->id, 'include' => 'registerItems']);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);

        $data = $this->getItemData($work);

        $response->assertJson(['data' => $data]);
    }
}
