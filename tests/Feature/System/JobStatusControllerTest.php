<?php

namespace Tests\Feature\System;

use App\Models\System\JobStatus;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class JobStatusControllerTest extends MyTestCase
{
    public function testShowByJobId(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $progress = 37;
        $jobStatus = JobStatus::factory()->create(['job_id' => Str::uuid(), 'progress_now' => $progress]);

        $routeName = 'my.job-statuses.show.by.job';

        $response = $this->post(route($routeName, ['jobId' => $jobStatus->job_id]))
            ->assertSuccessful();

        $response->assertSee($jobStatus->job_id);
        // progress bar should have style, width: 37%
        $response->assertSee($progress);
    }
}
