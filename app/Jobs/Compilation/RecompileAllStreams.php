<?php

namespace App\Jobs\Compilation;

use App\Models\Customer\Norma;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecompileAllStreams implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected bool $autoCompiledOnly = false)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');

        Norma::active()->when($this->autoCompiledOnly, function ($q) {
            $q->where('auto_compiled', true);
        })
            ->chunk(10, function ($streams) {
                foreach ($streams as $norma) {
                    HandleNormaRecompilation::dispatch($norma->id)->onQueue('compilation');
                    $norma->updateNeedsRecompilation(false);
                    // just slow it down slightly not to overwhelm redis, but the job can't timeout, so can't be too slow
                    usleep(5000);
                }
            });
    }
}
