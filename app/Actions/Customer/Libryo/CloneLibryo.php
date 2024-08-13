<?php

namespace App\Actions\Customer\Libryo;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\ContextQuestionLibryo;
use App\Models\Customer\Pivots\LegalDomainLibryo;
use App\Models\Customer\Pivots\LibryoReference;
use App\Models\Customer\Pivots\LibryoRequirementsCollection;
use App\Models\Customer\Pivots\LibryoWork;
use Illuminate\Support\Facades\DB;
use Throwable;

class CloneLibryo
{
    /**
     * Clone the given libryo and return the clone.
     *
     * @param \App\Models\Customer\Libryo $libryo
     *
     * @throws Throwable
     *
     * @return \App\Models\Customer\Libryo
     */
    public function handle(Libryo $libryo): Libryo
    {
        /** @var Libryo */
        return DB::transaction(function () use ($libryo) {
            $clone = $this->replicate($libryo);

            $this->updateAndPersist($libryo, $clone, ContextQuestionLibryo::class);
            $this->updateAndPersist($libryo, $clone, LegalDomainLibryo::class);
            $this->updateAndPersist($libryo, $clone, LibryoRequirementsCollection::class);
            $this->updateAndPersist($libryo, $clone, LibryoReference::class);
            $this->updateAndPersist($libryo, $clone, LibryoWork::class);
            $this->updateAndPersist($libryo, $clone, CompilationSetting::class);

            return $clone;
        });
    }

    /**
     * Clone the given libryo.
     *
     * @param \App\Models\Customer\Libryo $libryo
     *
     * @return \App\Models\Customer\Libryo
     */
    protected function replicate(Libryo $libryo): Libryo
    {
        $clone = $libryo->replicate();
        $clone->title = "{$clone->title} - Copy";
        $clone->integration_id = null;

        $clone->save();

        return $clone;
    }

    /**
     * Update the place_id and persist the new items.
     *
     * @param \App\Models\Customer\Libryo $libryo
     * @param \App\Models\Customer\Libryo $clone
     * @param class-string                $model
     *
     * @return void
     */
    protected function updateAndPersist(Libryo $libryo, Libryo $clone, string $model): void
    {
        /** @var LibryoRequirementsCollection $model */
        $toInsert = $model::where('place_id', $libryo->id)
            ->get()
            ->map(function ($item) use ($clone) {
                /** @var LibryoRequirementsCollection $item */
                $item->place_id = $clone->id;
                $item = $item->toArray();
                unset($item['id']);

                return $item;
            })
            ->all();

        $model::insertOrIgnore($toInsert);
    }
}
