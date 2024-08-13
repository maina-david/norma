<?php

namespace Tests\Feature\Assess\My;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Enums\System\LibryoModule;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use Tests\Feature\My\MyTestCase;

class AssessmentItemControllerTest extends MyTestCase
{
    public function testDashboardNoActivity(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.dashboard';

        // redirect for assess not enabled
        $response = $this->get(route($routeName))->assertRedirect();
        $libryo->enableModule(LibryoModule::comply());

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('Assess has not been set up for ' . $libryo->title);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('Assess has not been set up for ' . $org->title);
    }

    public function testDashboardWithActivity(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.dashboard';

        // redirect for assess not enabled
        $response = $this->get(route($routeName))->assertRedirect();
        $libryo->enableModule(LibryoModule::comply());

        $response = $this->get(route($routeName))->assertSuccessful();

        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('Last Activity Date');
        $response->assertSeeSelector('//div//div//span//text()[contains(.,"High")]');
    }

    public function testShow(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.assess.assessment-item.show';
        $libryo2 = Libryo::factory()->for($org)->create();
        $libryo3 = Libryo::factory()->for($org)->create();
        $user->libryos()->attach($libryo2);

        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($item)->create(['answer' => ResponseStatus::no()->value]);

        $aiResponse2 = AssessmentItemResponse::factory()->for($libryo2)->for($item)->create(['answer' => ResponseStatus::yes()->value]);
        $aiResponse3 = AssessmentItemResponse::factory()->for($libryo3)->for($item)->create(['answer' => ResponseStatus::yes()->value]);

        // redirect for assess not enabled
        $response = $this->get(route($routeName, ['assessmentItem' => $item]))->assertRedirect();
        $libryo->enableModule(LibryoModule::comply());

        $response = $this->get(route($routeName, ['assessmentItem' => $item]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $this->activateAllStreams($user);

        $response = $this->get(route($routeName, ['assessmentItem' => $item]))->assertSuccessful();
        $response->assertSee($item->toDescription());
        // should see SAI title with libryo title
        $response->assertSee($libryo2->title);
        // user doesn't have access to libryo3, so shouldn't see
        $response->assertDontSee($libryo3->title);
    }
}
