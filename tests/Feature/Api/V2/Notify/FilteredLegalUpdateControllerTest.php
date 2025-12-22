<?php

namespace Tests\Feature\Api\V2\Notify;

use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\Api\ApiTestCase;

class FilteredLegalUpdateControllerTest extends ApiTestCase
{
    public function testIndexFiltered(): void
    {
        [$norma, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $releaseAt = now()->subDay()->format('Y-m-d');
        $update = LegalUpdate::factory()->for($work)->create(['release_at' => $releaseAt, 'primary_location_id' => $requirementsCollection->id]);
        $user = User::factory()->create();
        $update->normas()->attach($norma);
        $update->users()->attach($user);
        $highlights = '<p>Highlights</p>';
        $work->update(['highlights' => $highlights]);
        $work->refresh();

        $update->legalDomains()->attach($domain);

        $user->normas()->attach($norma);

        $routeName = 'api.v2.notify.legal-updates.filtered.index';
        $route = route($routeName, ['page' => 1, 'perPage' => 5, 'count' => 'places', 'places' => [$norma->id]]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'get', $route);
        $items = [];

        $items[] = [
            'id' => $update->id,
            'hash_id' => $update->hash_id,
            'title' => $update->title,
            'notification_date' => $update->notification_date->format('Y-m-d'),
            'interpretation' => $highlights,
            'release_at' => $releaseAt,
            'work' => [
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
            ],
            'updated_at' => $update->updated_at->format('Y-m-d H:i:s'),
            'created_at' => $update->created_at->format('Y-m-d H:i:s'),
            'sent_to_user' => true,
            'read_status' => false,
            'understood_status' => false,
            'places_count' => 1,
        ];

        $response->assertJson([
            'data' => $items,
        ], true);

        $this->deleteCompiledStream();
    }
}
