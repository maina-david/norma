<?php

namespace App\Jobs\Compilation;

use App\Models\Customer\Libryo;
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
        /** @var Collection<Libryo> */
        $streams = Libryo::where('needs_recompilation', true)
            ->get(['id', 'needs_recompilation']);

        foreach ($streams as $libryo) {
            HandleLibryoRecompilation::dispatch($libryo->id)
                ->onQueue('compilation');
            $libryo->updateNeedsRecompilation(false);
        }
    }
}
