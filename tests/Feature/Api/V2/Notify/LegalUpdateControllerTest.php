<?php

namespace Tests\Feature\Api\V2\Notify;

use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\Api\ApiTestCase;

class LegalUpdateControllerTest extends ApiTestCase
{
    public function testIndexFiltered(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $releaseAt = now()->subDay()->format('Y-m-d');
        $releaseAtFuture = now()->addMonths(2)->format('Y-m-d');
        $update = LegalUpdate::factory()->for($work)->create(['release_at' => $releaseAt, 'status' => LegalUpdatePublishedStatus::PUBLISHED->value]);
        $update2 = LegalUpdate::factory()->for($work)->create(['release_at' => $releaseAtFuture, 'status' => LegalUpdatePublishedStatus::PUBLISHED->value]);
        $user = User::factory()->create();
        $update->libryos()->attach($libryo);
        $update->users()->attach($user);

        $highlights = '<p>Highlights</p>';
        $work->update(['highlights' => $highlights]);
        $work->refresh();

        $user->libryos()->attach($libryo);

        $routeName = 'api.v2.notify.legal-updates.index';
        $lastWeek = now()->subDays(7)->format('Y-m-d');
        $nextWeek = now()->addDays(7)->format('Y-m-d');
        $route = route($routeName, ['page' => 1, 'perPage' => 5, 'include' => 'libryos', 'filters' => ['release_at,gt,' . $lastWeek . '|release_at,lt,' . $nextWeek]]);
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
        ];

        $response->assertJson([
            'data' => $items,
        ], true);

        $response->assertJsonMissing([
            'data' => [
                'id' => $update2->id,
            ],
        ], true);

        $this->deleteCompiledStream();
    }

    public function testMarkAsUnderstood(): void
    {
        $update = LegalUpdate::factory()->create();
        $user = User::factory()->create();
        $user->legalUpdates()->attach($update);
        $route = route('api.v2.notify.legal-updates.understood', ['update' => $update]);
        $this->assertApiUnauthorizedThenRun($user, 'patch', $route)
            ->assertSuccessful();

        $update = $user->legalUpdates()->get()->first();
        $this->assertTrue($update->pivot->understood_status);
    }
}
