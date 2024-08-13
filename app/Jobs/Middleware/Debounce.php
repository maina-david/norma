<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Cache;

class Debounce
{
    /** @var bool */
    protected static bool $fake = false;

    /**
     * @param string $lockKey    The key to be used to indicate that the process is running
     * @param int    $debounce
     * @param int    $jobTimeout
     * @param bool   $cleanUp
     */
    public function __construct(protected string $lockKey, protected int $debounce, protected int $jobTimeout, protected bool $cleanUp = true)
    {
    }

    /**
     * Fake the debouce for testing.
     *
     * @param bool $value
     *
     * @return void
     */
    public static function fake(bool $value = true): void
    {
        self::$fake = $value;
    }

    /**
     * Get the key to be used to mark a request.
     *
     * @param string $localKey
     *
     * @return string
     */
    protected static function requestKey(string $localKey): string
    {
        return "request_{$localKey}";
    }

    /**
     * Process the queued job.
     *
     * @param mixed    $job
     * @param callable $next
     *
     * @return void
     */
    public function handle(mixed $job, callable $next): void
    {
        if (self::$fake) {
            $next($job);

            return;
        }

        $requestKey = self::requestKey($this->lockKey);
        // if a request has not been made before, create the request and wait to see if another request comes through.
        if (Cache::missing($requestKey)) {
            Cache::put($requestKey, true, $this->jobTimeout - 10);
            Cache::put($this->lockKey, true, $this->debounce);

            $job->release($this->debounce + 5);

            return;
        }

        // if a request exists, check if it is locked. If it is, discard this job as another one is waiting
        // to be processed.
        if (Cache::has($this->lockKey)) {
            return;
        }

        // A request key exists and the lock key does not exist, so we can set up the lock key and process this job.
        // The lock key should last for the duration of the job to prevent other jobs from running.
        Cache::put($requestKey, true, $this->jobTimeout - 10);
        Cache::put($this->lockKey, true, $this->jobTimeout - 10);

        $next($job);

        if ($this->cleanUp) {
            self::cleanUp($this->lockKey);
        }
    }

    /**
     * Clean up the locks.
     *
     * @param string $lockKey
     *
     * @return void
     */
    public static function cleanUp(string $lockKey): void
    {
        Cache::forget(self::requestKey($lockKey));
        Cache::forget($lockKey);
    }
}
