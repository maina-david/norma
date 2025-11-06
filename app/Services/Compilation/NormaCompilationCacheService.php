<?php

namespace App\Services\Compilation;

use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use App\Stores\Compilation\ContextQuestionNormaStore;
use App\Stores\Compilation\LibraryReferenceStore;
use App\Stores\Customer\NormaReferenceStore;
use App\Stores\Customer\NormaWorkStore;

class NormaCompilationCacheService
{
    public function __construct(
        protected ContextQuestionNormaStore $contextQuestionNormaStore,
        protected NormaReferenceStore $normaReferenceStore,
        protected LibraryReferenceStore $libraryReferenceStore,
        protected NormaWorkStore $normaWorkStore
    ) {
    }

    /**
     * Recompiles the citation cache for the embryo of a norma stream.
     *
     * @param Norma $norma
     */
    public function handleNormaRecompilation(Norma $norma): void
    {
        if ($norma->auto_compiled && $norma->location) {
            $ids = $this->getRefIdsForAutoCompiled($norma);
        } else {
            $ids = $this->getRefIdsForManual($norma);
        }

        $this->normaReferenceStore->syncReferences($norma, $ids);
        $this->normaWorkStore->syncWorksForReferences($norma, $ids);
        $this->applyApplicabilityChanges($norma, $ids);

        $norma->update(['compiled_at' => now()]);
    }

    /**
     * @param Norma $norma
     *
     * @return array<int, int>
     */
    public function getRefIdsForAutoCompiled(Norma $norma): array
    {
        $norma->load(['compilationSetting']);

        if ($norma->compilationSetting->use_context_questions) {
            // get possible context questions for norma
            $contextQuestionIds = ContextQuestion::whereHas('references', function ($q) use ($norma) {
                $q->forNormaAutocompiledBase($norma);
            })->pluck('id')->all();

            $this->contextQuestionNormaStore->syncContextQuestions($norma, $contextQuestionIds);
        }

        $ids = Reference::forNormaAutocompiled($norma)->pluck('id')->toArray();

        if ($norma->library) {
            $this->libraryReferenceStore->syncReferences($norma->library, $ids);
        }

        return $ids;
    }

    /**
     * @param Norma $norma
     *
     * @return array<int, int>
     */
    public function getRefIdsForManual(Norma $norma): array
    {
        return (new Reference())->newQuery()
            ->inNormaLibraries($norma)
            ->forActiveWork()
            ->active()
            ->compilable()
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param \App\Models\Customer\Norma $norma
     * @param array<int, int>             $ids
     *
     * @return void
     */
    protected function applyApplicabilityChanges(Norma $norma, array $ids): void
    {
        $exclude = Reference::forNormaExcluded($norma)->pluck('id')->all();
        $include = Reference::forNormaIncluded($norma)->pluck('id')->all();

        $ids = collect($ids)->filter(fn ($item) => !in_array($item, $exclude))->values()->all();

        $ids = [...$ids, ...$include];

        $this->normaReferenceStore->syncLiveReferences($norma, $ids);
        $this->normaWorkStore->syncLiveWorksForReferences($norma, $ids);
    }
}
