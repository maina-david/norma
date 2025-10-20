<?php

namespace App\Jobs\Compilation;

use App\Models\Customer\Norma;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecompileStreamsNeedingRecompilation implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Collection<Norma> */
        $streams = Norma::where('needs_recompilation', true)
            ->get(['id', 'needs_recompilation']);

        foreach ($streams as $norma) {
            HandleNormaRecompilation::dispatch($norma->id)
                ->onQueue('compilation');
            $norma->updateNeedsRecompilation(false);
        }
    }
}
