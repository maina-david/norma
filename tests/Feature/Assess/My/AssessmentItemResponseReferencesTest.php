<?php

namespace Tests\Feature\Assess\My;

use App\Enums\System\LibryoModule;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Tests\Feature\My\MyTestCase;

class AssessmentItemResponseReferencesTest extends MyTestCase
{
    public function testManagement(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::comply());

        $org2 = Organisation::factory()->create();
        $nonOrg = Libryo::factory()->for($org2)->create();

        $item = AssessmentItem::factory()->create(['organisation_id' => $org->id]);
        $response = AssessmentItemResponse::factory()->for($item)->create(['place_id' => $libryo->id]);

        /** @var Work $work */
        $work = Work::factory()->create();
        $work->libryos()->attach($libryo->id);

        /** @var Reference $reference */
        $reference = Reference::factory()->for($work)->create();
        $reference->load(['refPlainText']);

        $this->get(route('my.references.for.assessment-item-response.work.create', ['response' => $response->id, 'work' => $work->id]))
            ->assertSuccessful()
            ->assertDontSee($reference->refPlainText->plain_text);

        $reference->libryos()->attach($libryo->id);

        $this->get(route('my.references.for.assessment-item-response.work.create', ['response' => $response->id, 'work' => $work->id]))
            ->assertSuccessful()
            ->assertSee($reference->refPlainText->plain_text);

        $redirect = $this->post(route('my.references.for.assessment-item-response.store', ['response' => $response->id]), ['reference_id' => $reference->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas(AssessmentItemReference::class, [
            'assessment_item_id' => $item->id,
            'reference_id' => $reference->id,
            'source' => 'UG-SAI',
        ]);

        $this->followRedirects($redirect)
            ->assertSuccessful()
            ->assertSee($reference->refPlainText->plain_text);

        $this->get(route('my.references.for.assessment-item-response.create', ['response' => $response->id]))
            ->assertSuccessful()
            ->assertSee('Select Requirement Document')
            ->assertSee($work->title);

        $this->get(route('my.assess.assessment-activities.index.for.response', ['response' => $response->id]))
            ->assertSee('Linked requirement: ' . $reference->refPlainText->plain_text);

        $this->delete(route('my.references.for.assessment-item-response.destroy', ['response' => $response->id, 'reference' => $reference->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing(AssessmentItemReference::class, [
            'assessment_item_id' => $item->id,
            'reference_id' => $reference->id,
        ]);

        $this->get(route('my.assess.assessment-activities.index.for.response', ['response' => $response->id]))
            ->assertSee('Unlinked requirement: ' . $reference->refPlainText->plain_text);
    }
}
