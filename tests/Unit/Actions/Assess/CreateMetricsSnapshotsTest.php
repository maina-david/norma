<?php

namespace Tests\Unit\Actions\Assess;

use App\Actions\Assess\CreateMetricsSnapshotForLibryo;
use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Libryo;
use Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateMetricsSnapshotsTest extends TestCase
{
    public function testHandle(): void
    {
        Queue::fake();

        $libryo = Libryo::factory()->create();
        $aiItem = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($libryo)->for($aiItem)->create(['answer' => ResponseStatus::no()->value]);

        Artisan::call('assess:create-metrics-snapshots');

        CreateMetricsSnapshotForLibryo::assertPushed();
    }
}
