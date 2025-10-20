<?php

namespace App\Jobs\Compilation;

use App\Models\Customer\Norma;
use App\Services\Compilation\NormaCompilationCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class HandleNormaRecompilation implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected int $normaId)
    {
        $this->onQueue('compilation');
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<WithoutOverlapping>
     */
    public function middleware()
    {
        return [
            (new WithoutOverlapping((string) $this->normaId))->dontRelease()->expireAfter($this->timeout),
        ];
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return (string) $this->normaId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(NormaCompilationCacheService $cacheService)
    {
        /** @var Norma */
        $norma = Norma::findOrFail($this->normaId);

        $cacheService->handleNormaRecompilation($norma);
    }
}
