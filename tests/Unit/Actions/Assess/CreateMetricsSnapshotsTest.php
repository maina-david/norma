<?php

namespace Tests\Unit\Actions\Assess;

use App\Actions\Assess\CreateMetricsSnapshotForNorma;
use App\Enums\Assess\ResponseStatus;
use App\Enums\Assess\RiskRating;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Customer\Norma;
use Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateMetricsSnapshotsTest extends TestCase
{
    public function testHandle(): void
    {
        Queue::fake();

        $norma = Norma::factory()->create();
        $aiItem = AssessmentItem::factory()->create(['risk_rating' => RiskRating::high()->value]);
        $aiResponse = AssessmentItemResponse::factory()->for($norma)->for($aiItem)->create(['answer' => ResponseStatus::no()->value]);

        Artisan::call('assess:create-metrics-snapshots');

        CreateMetricsSnapshotForNorma::assertPushed();
    }
}
