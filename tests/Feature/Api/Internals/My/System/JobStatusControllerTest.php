<?php

namespace Tests\Feature\Api\Internals\My\System;

use App\Models\System\JobStatus;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class JobStatusControllerTest extends MyTestCase
{
    public function testViewingJobDetails(): void
    {
        $this->initUserNormaOrg();
        $progress = 55;
        $jobStatus = JobStatus::factory()->create(['job_id' => Str::uuid(), 'progress_now' => $progress]);

        $this->post(route('api.my.job-statuses.show.by.job', ['job' => $jobStatus->job_id]))
            ->assertSuccessful()
            ->assertExactJson([
                'data' => [
                    'job' => $jobStatus->job_id,
                    'percentage' => $progress,
                ],
            ]);
    }
}
