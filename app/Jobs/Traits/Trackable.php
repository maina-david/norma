<?php

namespace App\Jobs\Traits;

use App\Enums\System\JobStatusType;
use App\Models\System\JobStatus;
use Exception;

trait Trackable
{
    /** @var int|null */
    protected $statusId;

    /** @var JobStatus|null */
    protected $jobStatus;

    /**
     * @param int $value
     *
     * @return void
     */
    protected function setProgressMax(int $value): void
    {
        $this->updateJobStatus(['progress_max' => $value]);
    }

    /**
     * @param int $value
     * @param int $every
     *
     * @return void
     */
    protected function setProgressNow(int $value, int $every = 1): void
    {
        if ($value % $every == 0) {
            $this->updateJobStatus(['progress_now' => $value]);
        }
    }

    // /**
    //  * @param array<mixed> $value
    //  *
    //  * @return void
    //  */
    // protected function setJobStatusInput(array $value): void
    // {
    //     $this->updateJobStatus(['console_input' => $value]);
    // }

    // /**
    //  * @param array<mixed> $value
    //  *
    //  * @return void
    //  */
    // protected function setJobStatusOutput(array $value): void
    // {
    //     $this->updateJobStatus(['console_output' => $value]);
    // }

    /**
     * @param array<mixed> $data
     *
     * @return bool
     */
    protected function updateJobStatus(array $data): bool
    {
        if (is_null($this->jobStatus)) {
            // @codeCoverageIgnoreStart
            /** @var JobStatus|null */
            $jobStatus = JobStatus::find($this->statusId);
            $this->jobStatus = $jobStatus;
            // @codeCoverageIgnoreEnd
        }

        if (!is_null($this->jobStatus)) {
            return $this->jobStatus->update($data);
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return void
     */
    protected function prepareStatus(): void
    {
        /** @var JobStatus|null */
        $jobStatus = JobStatus::create([
            'job_type' => static::class,
            'status' => JobStatusType::queued()->value,
        ]);
        $this->jobStatus = $jobStatus;
        $this->statusId = $this->jobStatus?->id;
    }

    // /**
    //  * @param int $id
    //  *
    //  * @return void
    //  */
    // public function setJobStatusId(int $id): void
    // {
    //     $this->statusId = $id;
    // }

    /**
     * @return int
     */
    public function getJobStatusId(): int
    {
        if ($this->statusId == null) {
            // @codeCoverageIgnoreStart
            throw new Exception('Failed to get jobStatusId');
            // @codeCoverageIgnoreEnd
        }

        return $this->statusId;
    }

    // /**
    //  * @param array<mixed> $output
    //  *
    //  * @return bool
    //  */
    // public function setJobOutput(array $output): bool
    // {
    //     if ($jobStatus = JobStatus::find($this->statusId)) {
    //         return (bool) $jobStatus->update([
    //             'console_output' => json_encode($output),
    //         ]);
    //     }

    //     return false;
    // }
}
