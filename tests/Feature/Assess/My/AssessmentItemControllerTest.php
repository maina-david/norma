<?php

namespace Tests\Feature\Assess\My;

use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Enums\System\NormaModule;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
use Tests\Feature\My\MyTestCase;

class AssessmentItemControllerTest extends MyTestCase
{
    public function testDashboardNoActivity(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.assess.dashboard';

        // redirect for assess not enabled
        $response = $this->get(route($routeName))->assertRedirect();
        $norma->enableModule(NormaModule::comply());

        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('Assess has not been set up for ' . $norma->title);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('Assess has not been set up for ' . $org->title);
    }

    public function testDashboardWithActivity(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.assess.dashboard';

        // redirect for assess not enabled
        $response = $this->get(route($routeName))->assertRedirect();
        $norma->enableModule(NormaModule::comply());

        $response = $this->get(route($routeName))->assertSuccessful();

        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($norma)->for($item)->create(['answer' => ResponseStatus::no()->value]);

        $this->activateAllStreams($user);
        $response = $this->get(route($routeName))->assertSuccessful();
        $response->assertSee('Last Activity Date');
        $response->assertSeeSelector('//div//div//span//text()[contains(.,"High")]');
    }

    public function testShow(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.assess.assessment-item.show';
        $norma2 = Norma::factory()->for($org)->create();
        $norma3 = Norma::factory()->for($org)->create();
        $user->normas()->attach($norma2);

        $item = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($norma)->for($item)->create(['answer' => ResponseStatus::no()->value]);

        $aiResponse2 = AssessmentItemResponse::factory()->for($norma2)->for($item)->create(['answer' => ResponseStatus::yes()->value]);
        $aiResponse3 = AssessmentItemResponse::factory()->for($norma3)->for($item)->create(['answer' => ResponseStatus::yes()->value]);

        // redirect for assess not enabled
        $response = $this->get(route($routeName, ['assessmentItem' => $item]))->assertRedirect();
        $norma->enableModule(NormaModule::comply());

        $response = $this->get(route($routeName, ['assessmentItem' => $item]))->assertSuccessful();
        $response->assertSee($item->toDescription());

        $this->activateAllStreams($user);

        $response = $this->get(route($routeName, ['assessmentItem' => $item]))->assertSuccessful();
        $response->assertSee($item->toDescription());
        // should see SAI title with norma title
        $response->assertSee($norma2->title);
        // user doesn't have access to norma3, so shouldn't see
        $response->assertDontSee($norma3->title);
    }
}
