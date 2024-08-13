<?php

namespace App\Http\Controllers\Api\Internals\My\System;

use App\Models\System\JobStatus;
use Illuminate\Http\JsonResponse;

class JobStatusController
{
    /**
     * Get the job status.
     *
     * @param string $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $job): JsonResponse
    {
        /** @var JobStatus $jobStatus */
        $jobStatus = JobStatus::where('id', $job)->orWhere('job_id', $job)->firstOrFail();

        return response()->json([
            'data' => [
                'job' => $job,
                'percentage' => $jobStatus->progress_now ?? 0,
            ],
        ]);
    }
}
