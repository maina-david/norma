<?php

namespace Tests\Feature\Api\V2\Notify;

use App\Models\Auth\User;
use App\Models\Notify\LegalUpdate;
use Tests\Feature\Api\ApiTestCase;

class LegalUpdateForLibryosControllerTest extends ApiTestCase
{
    public function testIndexFiltered(): void
    {
        [$libryo, $organisation, $work, $requirementsCollection, $domain, $tag] = $this->initCompiledStream();

        $releaseAt = now()->subDay()->format('Y-m-d');
        $update = LegalUpdate::factory()->for($work)->create(['release_at' => $releaseAt]);
        $user = User::factory()->create();
        $update->libryos()->attach($libryo);
        $update->users()->attach($user);
        $highlights = '<p>Highlights</p>';
        $work->update(['highlights' => $highlights]);

        $user->libryos()->attach($libryo);

        $routeName = 'api.v2.notify.legal-updates.index.for.libryos';
        $route = route($routeName, ['start_date' => now()->subDays(7)->format('Y-m-d'), 'end_date' => now()->addDay()->format('Y-m-d')]);
        $response = $this->assertApiUnauthorizedThenRun($user, 'post', $route, ['libryos' => [$libryo->id]]);
        $items = [];

        $items[] = [
            'id' => $update->id,
            'hash_id' => $update->hash_id,
            'title' => $update->title,
            'notification_date' => $update->notification_date->format('Y-m-d'),
            'libryos' => [$libryo->id],
            'interpretation' => $highlights,
            'effective_date' => $update->effective_date->format('Y-m-d'),
            'notice_number' => $work->notice_number ?? $update->publication_document_number ?? '',
            'work_type' => $work->work_type ?? null,
        ];

        $response->assertJson([
            'data' => $items,
        ], true);

        $this->deleteCompiledStream();
    }
}
