<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\JobStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JobStatusController extends Controller
{
    public function showByJobId(Request $request, string $jobId): Response
    {
        /** @var JobStatus $jobStatus */
        $jobStatus = JobStatus::where('id', $jobId)->orWhere('job_id', $jobId)->first();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => $request->get('turbo-target', 'download-progress'),
            'jobId' => $jobId,
            'percentage' => $jobStatus->progress_now ?? 0,
            'redirect' => $request->input('redirect'),
            'upload' => (bool) $request->input('upload', 0),
        ]);

        return turboStreamResponse($view);
    }
}
