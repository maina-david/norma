<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Illuminate\Support\Facades\DB;

class MigrateReferencesToAugModel
{
    /**
     * Migrate the reference positions.
     *
     * @param \App\Models\Corpus\Work $work
     *
     * @return void
     */
    public function handle(Work $work): void
    {
        if ($work->docs()->exists()) {
            $this->migrateReferencesForWork($work);
        }
    }

    /**
     * Change the volume and position of the references.
     *
     * @param \App\Models\Corpus\Work $work
     *
     * @return void
     */
    private function migrateReferencesForWork(Work $work): void
    {
        Reference::where('work_id', $work->id)
            ->where('volume', '>', 1)
            ->update([
                'volume' => 1,
                'level' => 1,
                'position' => DB::raw('((volume - 1) * 10000) + position'),
            ]);
    }
}
