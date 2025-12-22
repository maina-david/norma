<?php

namespace Tests\Feature\Api\V2\Corpus;

use App\Models\Auth\User;
use Tests\Feature\Api\ApiTestCase;

class ForOrganisationReferenceControllerTest extends ApiTestCase
{
    public function testForOrganisation(): void
    {
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $user = User::factory()->create();
        $user->normas()->attach($norma);
        $user->organisations()->attach($organisation);

        $routeName = 'api.v2.corpus.references.for.organisation';
        $route = route($routeName, ['organisation' => $organisation]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];

        $reference = $work->references->first()->load(['citation', 'htmlContent', 'refPlainText']);

        $item = [
            'id' => $reference->id,
            'citation_type' => null, // legacy
            'number' => $reference->refPlainText?->plain_text ? null : ($reference->citation->number ?? null),
            'register_id' => $reference->work_id,
            'title' => $reference->refPlainText?->plain_text ?: null,
            'sub_line' => null, // legacy
            'derived' => false,
            'position' => $reference->start,
            'is_toc_item' => true,
            'is_section' => $reference->is_section ?? ($reference->citation->is_section ?? false),
            'level' => $reference->level,
            'volume' => $reference->volume,
            'type' => $reference->type,
            'content' => $reference->htmlContent?->cached_content,
            'normas' => [['id' => $norma->id]],
            'created_at' => $reference->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $reference->updated_at->format('Y-m-d H:i:s'),
        ];

        $response->assertSuccessful();

        $content = $response->json();
        $this->assertSame($item['id'], $content['data'][0]['id']);
        foreach ($item as $key => $val) {
            if ($key === 'normas') {
                $this->assertTrue($val[0]['id'] === $norma->id);
                continue;
            }
            $this->assertSame($val, $content['data'][0][$key]);
        }

        $this->deleteCompiledStream();
    }
}
