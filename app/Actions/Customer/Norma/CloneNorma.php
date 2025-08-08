<?php

namespace App\Actions\Customer\Norma;

use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Norma;
use App\Models\Customer\Pivots\ContextQuestionNorma;
use App\Models\Customer\Pivots\LegalDomainNorma;
use App\Models\Customer\Pivots\NormaReference;
use App\Models\Customer\Pivots\NormaRequirementsCollection;
use App\Models\Customer\Pivots\NormaWork;
use Illuminate\Support\Facades\DB;
use Throwable;

class CloneNorma
{
    /**
     * Clone the given norma and return the clone.
     *
     * @param \App\Models\Customer\Norma $norma
     *
     * @throws Throwable
     *
     * @return \App\Models\Customer\Norma
     */
    public function handle(Norma $norma): Norma
    {
        /** @var Norma */
        return DB::transaction(function () use ($norma) {
            $clone = $this->replicate($norma);

            $this->updateAndPersist($norma, $clone, ContextQuestionNorma::class);
            $this->updateAndPersist($norma, $clone, LegalDomainNorma::class);
            $this->updateAndPersist($norma, $clone, NormaRequirementsCollection::class);
            $this->updateAndPersist($norma, $clone, NormaReference::class);
            $this->updateAndPersist($norma, $clone, NormaWork::class);
            $this->updateAndPersist($norma, $clone, CompilationSetting::class);

            return $clone;
        });
    }

    /**
     * Clone the given norma.
     *
     * @param \App\Models\Customer\Norma $norma
     *
     * @return \App\Models\Customer\Norma
     */
    protected function replicate(Norma $norma): Norma
    {
        $clone = $norma->replicate();
        $clone->title = "{$clone->title} - Copy";
        $clone->integration_id = null;

        $clone->save();

        return $clone;
    }

    /**
     * Update the place_id and persist the new items.
     *
     * @param \App\Models\Customer\Norma $norma
     * @param \App\Models\Customer\Norma $clone
     * @param class-string                $model
     *
     * @return void
     */
    protected function updateAndPersist(Norma $norma, Norma $clone, string $model): void
    {
        /** @var NormaRequirementsCollection $model */
        $toInsert = $model::where('place_id', $norma->id)
            ->get()
            ->map(function ($item) use ($clone) {
                /** @var NormaRequirementsCollection $item */
                $item->place_id = $clone->id;
                $item = $item->toArray();
                unset($item['id']);

                return $item;
            })
            ->all();

        $model::insertOrIgnore($toInsert);
    }
}
