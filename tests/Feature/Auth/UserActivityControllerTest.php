<?php

namespace Tests\Feature\Auth;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\UserActivity;
use Tests\Feature\My\MyTestCase;

class UserActivityControllerTest extends MyTestCase
{
    public function testTrackEvent(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $routeName = 'my.user.activities.track';

        $data = [
            'type' => UserActivityType::viewedTabSummary()->value,
            'details' => ['id' => 1],
        ];
        $response = $this->post(route($routeName), $data)->assertSuccessful();
        $activity = UserActivity::all()->last();
        $this->assertSame($user->id, $activity->user_id);
        $this->assertSame($libryo->id, $activity->place_id);
        $this->assertSame($org->id, $activity->organisation_id);
    }
}
