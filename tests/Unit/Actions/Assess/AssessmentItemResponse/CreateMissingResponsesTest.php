<?php

namespace Tests\Unit\Actions\Assess\AssessmentItemResponse;

use App\Actions\Assess\AssessmentItemResponse\CreateMissingResponses;
use App\Enums\System\LibryoModule;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Tests\TestCase;

class CreateMissingResponsesTest extends TestCase
{
    public function testHandle(): void
    {
        $org = Organisation::factory()->create();
        $libryo = Libryo::factory()->for($org)->create();
        $libryo->enableModule(LibryoModule::comply());
        $ref = Reference::factory()->create();
        $ref->libryos()->attach($libryo->id);
        $missingItem = AssessmentItem::factory()->create();

        $ai2 = AssessmentItem::factory()->create();
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($ai2)->create();
        $ai2->references()->attach($ref->id);

        $count = AssessmentItemResponse::count();
        $countActivities = AssessmentActivity::count();
        app(CreateMissingResponses::class)->handle($org);

        $this->assertSame($count, AssessmentItemResponse::count());
        $this->assertSame($countActivities, AssessmentActivity::count());

        $missingItem->references()->attach($ref->id);
        app(CreateMissingResponses::class)->handle($org);
        $this->assertGreaterThan($count, AssessmentItemResponse::count());
        $this->assertGreaterThan($countActivities, AssessmentActivity::count());
    }
}
