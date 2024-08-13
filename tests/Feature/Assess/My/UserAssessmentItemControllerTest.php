<?php

namespace Tests\Feature\Assess\My;

use App\Enums\Assess\ReassessInterval;
use App\Enums\Assess\RiskRating;
use App\Enums\System\LibryoModule;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\LegalDomain;
use App\Services\Customer\ActiveLibryosManager;
use Tests\Feature\My\MyTestCase;

class UserAssessmentItemControllerTest extends MyTestCase
{
    public function testManagement(): void
    {
        /** @var Libryo $libryo */
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $libryo->enableModule(LibryoModule::comply());
        $libryo2 = Libryo::factory()->for($org)->create();
        $org2 = Organisation::factory()->create();
        $nonOrg = Libryo::factory()->for($org2)->create();

        $domain = LegalDomain::factory()->create();
        LegalDomain::factory()->create(['parent_id' => $domain->id]);

        $libryo->legalDomains()->attach($domain->id);

        $this->get(route('my.assess.create'))
            ->assertSuccessful()
            ->assertSee('Item needs regular reassessment');

        $payload = [
            'title' => 'This is an awesome test',
            'risk_rating' => RiskRating::high()->value,
            'frequency' => 12,
            'frequency_interval' => ReassessInterval::MONTH->value,
        ];

        $this->post(route('my.assess.store'), $payload)->assertSessionHasNoErrors();

        $item = AssessmentItem::where('organisation_id', $org->id)->where('title', $payload['title'])->first();

        $this->assertNotNull($item);

        $this->assertDatabaseHas(AssessmentItemResponse::class, [
            'assessment_item_id' => $item->id,
            'place_id' => $libryo->id,
        ]);

        $this->assertDatabaseHas(AssessmentItemResponse::class, [
            'assessment_item_id' => $item->id,
            'place_id' => $libryo2->id,
        ]);

        $this->assertDatabaseMissing(AssessmentItemResponse::class, [
            'assessment_item_id' => $item->id,
            'place_id' => $nonOrg->id,
        ]);

        $this->get(route('my.assess.pending'))
            ->assertSuccessful()
            ->assertSee('This is an awesome test');

        app(ActiveLibryosManager::class)->activateAll($user);

        $payload = [
            'title' => 'This is another awesome test',
            'risk_rating' => RiskRating::high()->value,
            'frequency' => 12,
            'frequency_interval' => ReassessInterval::MONTH->value,
        ];

        $this->post(route('my.assess.store'), $payload)->assertSessionHasNoErrors();

        $this->get(route('my.assess.edit', ['assess' => $item->hash_id]))
            ->assertSuccessful()
            ->assertSee($item->title);

        $payload['title'] = 'Updated title over here';

        $this->put(route('my.assess.update', ['assess' => $item->hash_id]), $payload)
            ->assertSessionHasNoErrors();

        $this->get(route('my.assess.edit', ['assess' => $item->hash_id]))
            ->assertSuccessful()
            ->assertSee('Updated title over here');

        app(ActiveLibryosManager::class)->activate($user, $libryo);

        $payload['title'] = 'Updated again and again title over here';

        $this->put(route('my.assess.update', ['assess' => $item->hash_id]), $payload)
            ->assertSessionHasNoErrors();

        $this->get(route('my.assess.edit', ['assess' => $item->hash_id]))
            ->assertSuccessful()
            ->assertSee('Updated again and again title over here');

        $this->delete(route('my.assess.destroy', ['assess' => $item->hash_id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();
    }
}
