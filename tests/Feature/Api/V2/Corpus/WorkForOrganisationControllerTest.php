<?php

namespace Tests\Feature\Api\V2\Corpus;

use App\Models\Auth\User;
use Tests\Feature\Api\ApiTestCase;

class WorkForOrganisationControllerTest extends ApiTestCase
{
    public function testForOrganisation(): void
    {
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();
        $user = User::factory()->create();
        $user->normas()->attach($norma);
        $user->organisations()->attach($organisation);

        $routeName = 'api.v2.works.for.organisation';
        $route = route($routeName, ['organisation' => $organisation]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];

        $childWork = $work->children->first();
        foreach ([$work->load('parents'), $childWork->load('parents')] as $w) {
            $items[] = [
                'id' => $w->id,
                'iri' => $w->iri,
                'work_number' => $w->work_number,
                'title' => $w->title,
                'status' => (int) $w->status,
                'has_xml' => false, // legacy
                'work_type' => $w->getTypeName(),
                'sub_type' => $w->sub_type,
                'language_code' => $w->language_code,
                'enacted_date' => null, // legacy
                'publication_date' => null, // legacy
                'primary_location_id' => $w->primary_location_id,
                'title_translation' => $w->title_translation,
                'short_title' => $w->short_title,
                'highlights' => $w->highlights,
                'update_report' => $w->update_report,
                'issuing_authority' => $w->issuing_authority,
                'effective_date' => $w->effective_date?->format('Y-m-d') ?? null,
                'comment_date' => $w->comment_date?->format('Y-m-d') ?? null,
                'gazette_number' => $w->gazette_number,
                'notice_number' => $w->notice_number,
                'parent_ids' => $w->parents->modelKeys(),
            ];
        }

        $response->assertJson([
            'data' => $items,
        ], true);

        $this->deleteCompiledStream();
    }
}
