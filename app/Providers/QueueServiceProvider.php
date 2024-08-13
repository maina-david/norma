<?php

namespace App\Providers;

use App\Enums\System\JobStatusType;
use App\Models\System\JobStatus;
use Exception;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

/** @codeCoverageIgnore  */
class QueueServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        // Add Event listeners
        Queue::before(function (JobProcessing $event) {
            $jobId = $event->job->getJobId();
            $this->updateJobStatus($event->job, [
                'job_status' => JobStatusType::executing()->value,
                'job_id' => $jobId !== '' ? $jobId : uniqid('', true),
                'attempts' => $event->job->attempts(),
                'queue' => $event->job->getQueue(),
                'started_at' => Carbon::now(),
            ]);
        });
        Queue::after(function (JobProcessed $event) {
            $this->updateJobStatus($event->job, [
                'job_status' => JobStatusType::finished()->value,
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now(),
            ]);
        });
        Queue::failing(function (JobFailed $event) {
            $this->updateJobStatus($event->job, [
                'job_status' => JobStatusType::failed()->value,
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now(),
            ]);
        });
        Queue::exceptionOccurred(function (JobExceptionOccurred $event) {
            $this->updateJobStatus($event->job, [
                'job_status' => JobStatusType::failed()->value,
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now(),
                'output' => json_encode(['message' => $event->exception->getMessage()]),
            ]);
        });
    }

    /**
     * @param Job          $job
     * @param array<mixed> $data
     *
     * @return void
     */
    private function updateJobStatus(Job $job, array $data)
    {
        try {
            $payload = $job->payload();

            $trackableJob = unserialize($payload['data']['command']);

            try {
                if (!is_callable([$trackableJob, 'getJobStatusId'])) {
                    return;
                }
            } catch (Exception $e) {
                return;
            }

            // @phpstan-ignore-next-line
            $jobStatusId = $trackableJob->getJobStatusId();

            /** @var JobStatus|null */
            $jobStatus = JobStatus::find($jobStatusId);

            // clean up old statuses
            $this->deleteOldStatuses();

            if (!is_null($jobStatus)) {
                $jobStatus->update($data);
            }

            return;
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * @return void
     */
    private function deleteOldStatuses()
    {
        JobStatus::where('created_at', '<', Carbon::now()->subSeconds(config('queue.job-status.time-to-live')))
            ->ended()
            ->delete();
    }
}
