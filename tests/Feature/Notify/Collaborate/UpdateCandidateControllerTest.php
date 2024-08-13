<?php

namespace Tests\Feature\Notify\Collaborate;

use App\Enums\Corpus\UpdateCandidateActionStatus;
use App\Enums\Notify\AffectedLegislationType;
use App\Enums\Notify\NotificationStatus;
use App\Enums\Workflows\DocumentType;
use App\Enums\Workflows\YesNoPending;
use App\Models\Arachno\Source;
use App\Models\Corpus\Doc;
use App\Models\Corpus\DocMeta;
use App\Models\Geonames\Location;
use App\Models\Notify\NotificationAction;
use App\Models\Notify\NotificationHandover;
use App\Models\Notify\NotificationStatusReason;
use App\Models\Notify\Pivots\NotificationActionUpdateCandidate;
use App\Models\Notify\Pivots\NotificationHandoverUpdateCandidateLegislation;
use App\Models\Notify\UpdateCandidate;
use App\Models\Notify\UpdateCandidateLegislation;
use App\Models\Workflows\Document;
use App\Models\Workflows\Task;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UpdateCandidateControllerTest extends TestCase
{
    public function testTheResource(): void
    {
        $source = Source::factory()->create();
        $location = Location::factory()->create();
        $source->locations()->attach($location->id);

        $document = Document::factory()->create([
            'source_id' => $source->id,
            'document_type' => DocumentType::SOURCE_MONITORING->value,
        ]);
        $document->locations()->attach($location->id);

        $task = Task::factory()->create(['document_id' => $document->id]);

        $user = $this->collaboratorSignIn($this->collaborateSuperUser());

        $this->withoutExceptionHandling()
            ->get(route('collaborate.docs-for-update.tasks.create', ['task' => $task->id]))
            ->assertSee('Title')
            ->assertSee('English translation of the title (if not English)');

        $file = UploadedFile::fake()->create('test.pdf');

        $update = [
            'content_resource_file' => $file,
            'title' => $this->faker->sentence(),
            'source_url' => $this->faker->url(),
            'language_code' => 'swa',
            'primary_location_id' => $location->id,
            'document_date' => now()->format('Y-m-d'),
            'source_id' => $source->id,
        ];

        $this->assertDatabaseMissing(Doc::class, [
            'title' => $update['title'],
            'source_id' => $source->id,
        ]);

        $this->withoutExceptionHandling()
            ->post(route('collaborate.docs-for-update.tasks.store', ['task' => $task->id]), $update)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(Doc::class, [
            'title' => $update['title'],
            'source_id' => $source->id,
        ]);

        $doc = Doc::where('title', $update['title'])->where('source_id', $source->id)->first();

        $this->assertDatabaseHas(UpdateCandidate::class, ['doc_id' => $doc->id, 'author_id' => $user->id, 'manual' => true]);
        $this->assertDatabaseHas(DocMeta::class, ['doc_id' => $doc->id]);

        $oldTitle = $update['title'];
        $update['title'] = $this->faker->sentence();

        unset($update['content_resource_file']);

        $this->withExceptionHandling()
            ->get(route('collaborate.docs-for-update.tasks.index', ['task' => 0]))
            ->assertSuccessful();

        $this->withoutExceptionHandling()
            ->put(route('collaborate.docs-for-update.tasks.update', ['task' => $task->id, 'doc' => $doc->id]), $update)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseMissing(Doc::class, ['id' => $doc->id, 'title' => $oldTitle]);
        $this->assertDatabaseHas(Doc::class, ['id' => $doc->id, 'title' => $update['title']]);

        $this->withoutExceptionHandling()
            ->get(route('collaborate.docs-for-update.tasks.edit', ['task' => $task->id, 'doc' => $doc->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($update['title']);

        $this->withoutExceptionHandling()
            ->get(route('collaborate.docs-for-update.tasks.show', ['task' => $task->id, 'doc' => $doc->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($update['title']);

        $this->withoutExceptionHandling()
            ->get(route('collaborate.docs-for-update.tasks.index', ['task' => $task->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee($update['title']);

        /** Testing justification */
        $this->get(route('collaborate.docs-for-update.justification.create', ['doc' => $doc->id]))
            ->assertSuccessful()
            ->assertSee('Justification')
            ->assertSee('Populate with canned response');

        $this->withExceptionHandling()
            ->post(route('collaborate.docs-for-update.justification.store', ['doc' => $doc->id]), [])
            ->assertSessionHasErrors(['justification'])
            ->assertRedirect(route('collaborate.docs-for-update.justification.create', ['doc' => $doc->id]));

        $this->followingRedirects()
            ->post(route('collaborate.docs-for-update.justification.store', ['doc' => $doc->id]), ['justification_status' => 1, 'justification' => 'testing justification'])
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee('testing justification');

        $this->assertDatabaseHas(UpdateCandidate::class, ['doc_id' => $doc->id, 'justification' => 'testing justification']);

        // create a new doc
        $newDoc = Doc::factory()->create();

        $this->assertDatabaseMissing(UpdateCandidate::class, ['doc_id' => $newDoc->id]);

        $this->followingRedirects()
            ->post(route('collaborate.docs-for-update.justification.store', ['doc' => $newDoc->id]), ['justification_status' => 1, 'justification' => 'testing justification without doc'])
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee('testing justification without doc');

        $this->assertDatabaseHas(UpdateCandidate::class, ['doc_id' => $newDoc->id]);

        /** Testing Checked by Check Sources */
        $this->get(route('collaborate.docs-for-update.check.create', ['doc' => $doc->id]))
            ->assertSuccessful()
            ->assertSee('Comment')
            ->assertSee('checked_comment')
            ->assertSee('Populate with canned response');

        $this->withExceptionHandling()
            ->post(route('collaborate.docs-for-update.check.store', ['doc' => $doc->id]), [])
            ->assertSessionHasErrors(['checked_comment'])
            ->assertRedirect(route('collaborate.docs-for-update.check.create', ['doc' => $doc->id]));

        $this->followingRedirects()
            ->post(route('collaborate.docs-for-update.check.store', ['doc' => $doc->id]), ['check_sources_status' => 1, 'checked_comment' => 'testing checked_comment'])
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee('testing checked_comment');

        $this->assertDatabaseHas(UpdateCandidate::class, [
            'doc_id' => $doc->id,
            'checked_comment' => 'testing checked_comment',
            'checked_by_id' => $user->id,
        ]);

        /** Testing affected legislation */
        $this->get(route('collaborate.docs-for-update.affected.create', ['doc' => $doc->id]))
            ->assertSuccessful()
            ->assertSee('Add');

        $payload = [
            'affected_legislation' => [
                [
                    'title' => 'Some Legislation',
                    'source_url' => 'https://some-cool-source.com/legislation',
                    'type' => AffectedLegislationType::NEW->value,
                ],
            ],
        ];

        $this->followingRedirects()
            ->post(route('collaborate.docs-for-update.affected.store', ['doc' => $doc->id]), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee('https://some-cool-source.com/legislation');

        $this->assertDatabaseHas(UpdateCandidate::class, ['doc_id' => $doc->id, 'affected_available' => 1]);

        /** Testing notification status */
        $this->get(route('collaborate.docs-for-update.notification-status.create', ['doc' => $doc->id]))
            ->assertSuccessful()
            ->assertSee('Notification Status')
            ->assertSee('Reason');

        $this->withExceptionHandling()
            ->post(route('collaborate.docs-for-update.notification-status.store', ['doc' => $doc->id]), [])
            ->assertSessionHasErrors(['notification_status'])
            ->assertRedirect(route('collaborate.docs-for-update.notification-status.create', ['doc' => $doc->id]));

        $payload = [
            'notification_status' => NotificationStatus::REQUIRED->value,
            'notification_status_reason' => 'This is an awesome test',
        ];

        $this->followingRedirects()
            ->post(route('collaborate.docs-for-update.notification-status.store', ['doc' => $doc->id]), $payload)
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertSee('This is an awesome test');

        $reason = NotificationStatusReason::where('text', 'This is an awesome test')->first();

        $this->assertDatabaseHas(UpdateCandidate::class, [
            'doc_id' => $doc->id,
            'notification_status' => NotificationStatus::REQUIRED->value,
            'notification_status_reason_id' => $reason->id,
        ]);

        /** Testing additional actions */
        $reason = NotificationAction::factory()->create();

        $this->get(route('collaborate.docs-for-update.additional-actions.index', ['doc' => $doc->id]))
            ->assertSuccessful()
            ->assertSee('Update');

        $this->get(route('collaborate.docs-for-update.additional-actions.create', ['doc' => $doc->id]))
            ->assertSuccessful()
            ->assertSee('Select additional actions to add')
            ->assertSee($reason->text);

        $this->withoutExceptionHandling()
            ->post(route('collaborate.docs-for-update.additional-actions.sync', ['doc' => $doc->id]), ['additional_actions' => [$reason->id]])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.docs-for-update.additional-actions.create', ['doc' => $doc->id]));

        $this->withoutExceptionHandling()
            ->post(route('collaborate.docs-for-update.additional-actions.store', ['doc' => $doc->id]), ["action{$reason->id}" => UpdateCandidateActionStatus::DONE->value])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.docs-for-update.additional-actions.index', ['doc' => $doc->id]));

        $this->assertDatabaseHas(NotificationActionUpdateCandidate::class, [
            'doc_id' => $doc->id,
            'notification_action_id' => $reason->id,
            'status' => UpdateCandidateActionStatus::DONE->value,
        ]);

        $this->withoutExceptionHandling()
            ->delete(route('collaborate.docs-for-update.additional-actions.destroy', ['doc' => $doc->id, 'action' => $reason->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.docs-for-update.additional-actions.create', ['doc' => $doc->id]));

        $this->assertDatabaseMissing(NotificationActionUpdateCandidate::class, [
            'doc_id' => $doc->id,
            'notification_action_id' => $reason->id,
        ]);

        /** Testing handover actions */
        $reason = NotificationHandover::factory()->create();

        $this->get(route('collaborate.docs-for-update.handover-updates.create', ['doc' => $doc->id]))
            ->assertSuccessful()
            ->assertSee('Select handover actions to add')
            ->assertSee($reason->text);

        $legislation = UpdateCandidateLegislation::where('doc_id', $doc->id)->first();

        $this->withoutExceptionHandling()
            ->post(route('collaborate.docs-for-update.handover-updates.sync', ['doc' => $doc->id, 'legislation' => $legislation->id]), ['handover_update' => [$reason->id]])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.docs-for-update.handover-updates.create', ['doc' => $doc->id, 'activeTab' => "tab_{$legislation->id}"]));

        $this->withoutExceptionHandling()
            ->post(route('collaborate.docs-for-update.handover-updates.store', ['doc' => $doc->id, 'legislation' => $legislation->id]), ["action{$reason->id}" => UpdateCandidateActionStatus::DONE->value])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.docs-for-update.handover-updates.create', ['doc' => $doc->id, 'activeTab' => "tab_{$legislation->id}"]));

        $this->assertDatabaseHas(NotificationHandoverUpdateCandidateLegislation::class, [
            'update_candidate_legislation_id' => $legislation->id,
            'notification_handover_id' => $reason->id,
            'status' => UpdateCandidateActionStatus::DONE->value,
        ]);

        $this->withoutExceptionHandling()
            ->delete(route('collaborate.docs-for-update.handover-updates.destroy', ['doc' => $doc->id, 'legislation' => $legislation->id, 'update' => $reason->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('collaborate.docs-for-update.handover-updates.create', ['doc' => $doc->id, 'activeTab' => "tab_{$legislation->id}"]));

        $this->assertDatabaseMissing(NotificationHandoverUpdateCandidateLegislation::class, [
            'update_candidate_legislation_id' => $legislation->id,
            'notification_handover_id' => $reason->id,
        ]);

        /** End of tests */
        $this->withoutExceptionHandling()
            ->delete(route('collaborate.docs-for-update.tasks.destroy', ['task' => $task->id, 'doc' => $doc->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->withoutExceptionHandling()
            ->get(route('collaborate.docs-for-update.tasks.index', ['task' => $task->id]))
            ->assertSessionHasNoErrors()
            ->assertSuccessful()
            ->assertDontSee($update['title']);

        collect(YesNoPending::lang())->keys()->each(function ($value) use ($source) {
            $filters = [
                'jurisdiction' => Location::factory()->create()->id,
                'check-sources' => $value,
                'justification' => $value,
                'notification-status' => [1],
                'start-date' => '2022-12-01',
                'end-date' => '2022-12-07',
                'additional-actions' => NotificationAction::factory()->create()->id,
                'handover-update' => [NotificationHandover::factory()->create()->id],
                'handover-update-status' => [UpdateCandidateActionStatus::PENDING->value],
                'source' => $source->id,
                'task' => 0,
                'published' => 'Yes',
                'domains' => [0],
            ];

            $this->withoutExceptionHandling()
                ->get(route('collaborate.docs-for-update.tasks.index', $filters))
                ->assertSuccessful();
        });
    }
}
