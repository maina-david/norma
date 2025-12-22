<?php

namespace Tests\Feature\Assess\My;

use App\Enums\Assess\AssessActivityType;
use App\Enums\Assess\ResponseStatus;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Storage\My\File;
use Tests\Feature\My\MyTestCase;

class AssessmentActivityControllerTest extends MyTestCase
{
    public function testIndexForResponse(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $routeName = 'my.assess.assessment-activities.index.for.response';

        $assessmentItem = AssessmentItem::factory()->create();
        $response = AssessmentItemResponse::factory()->for($norma)->create(['last_answered_by' => $user->id]);

        $file = File::factory()->create();
        $this->createActivities($user, $norma, $response, $file, $assessmentItem);

        $response = $this->get(route($routeName, ['response' => $response]))->assertSuccessful();

        $response->assertSeeSelector('//p[text()[contains(.,"Assessment item was added")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the answer from ' . ResponseStatus::lang()[ResponseStatus::notAssessed()->value] . ' to ' . ResponseStatus::lang()[ResponseStatus::yes()->value] . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' changed the answer from ' . ResponseStatus::lang()[ResponseStatus::no()->value] . ' to ' . ResponseStatus::lang()[ResponseStatus::yes()->value] . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' posted a comment")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' uploaded ' . $file->title . '")]]');
        $response->assertSeeSelector('//p[text()[contains(.,"' . $user->fullName . ' uploaded Deleted File")]]');
    }

    private function createActivities($user, $norma, $response, $file, $assessmentItem): void
    {
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::responseAdded()->value,
            'to_status' => ResponseStatus::notAssessed()->value,
        ]);
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::answerChange()->value,
            'from_status' => ResponseStatus::notAssessed()->value,
            'to_status' => ResponseStatus::yes()->value,
        ]);
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::answerChange()->value,
            'from_status' => ResponseStatus::no()->value,
            'to_status' => ResponseStatus::yes()->value,
        ]);
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::answerChange()->value,
            'from_status' => null,
            'to_status' => ResponseStatus::yes()->value,
        ]);
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::answerChange()->value,
            'from_status' => ResponseStatus::notAssessed()->value,
            'to_status' => null,
        ]);
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::comment()->value,
        ]);
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::fileUpload()->value,
            'file_id' => $file->id,
        ]);
        AssessmentActivity::factory()->for($norma)->for($user)->for($response)->for($assessmentItem)->create([
            'activity_type' => AssessActivityType::fileUpload()->value,
            'file_id' => null,
        ]);
    }
}
