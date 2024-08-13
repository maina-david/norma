<?php

namespace App\Services\Compilation;

use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Stores\Compilation\ContextQuestionLibryoStore;
use App\Stores\Compilation\LibraryReferenceStore;
use App\Stores\Customer\LibryoReferenceStore;
use App\Stores\Customer\LibryoWorkStore;

class LibryoCompilationCacheService
{
    public function __construct(
        protected ContextQuestionLibryoStore $contextQuestionLibryoStore,
        protected LibryoReferenceStore $libryoReferenceStore,
        protected LibraryReferenceStore $libraryReferenceStore,
        protected LibryoWorkStore $libryoWorkStore
    ) {
    }

    /**
     * Recompiles the citation cache for the embryo of a libryo stream.
     *
     * @param Libryo $libryo
     */
    public function handleLibryoRecompilation(Libryo $libryo): void
    {
        if ($libryo->auto_compiled && $libryo->location) {
            $ids = $this->getRefIdsForAutoCompiled($libryo);
        } else {
            $ids = $this->getRefIdsForManual($libryo);
        }

        $this->libryoReferenceStore->syncReferences($libryo, $ids);
        $this->libryoWorkStore->syncWorksForReferences($libryo, $ids);
        $this->applyApplicabilityChanges($libryo, $ids);

        $libryo->update(['compiled_at' => now()]);
    }

    /**
     * @param Libryo $libryo
     *
     * @return array<int, int>
     */
    public function getRefIdsForAutoCompiled(Libryo $libryo): array
    {
        $libryo->load(['compilationSetting']);

        if ($libryo->compilationSetting->use_context_questions) {
            // get possible context questions for libryo
            $contextQuestionIds = ContextQuestion::whereHas('references', function ($q) use ($libryo) {
                $q->forLibryoAutocompiledBase($libryo);
            })->pluck('id')->all();

            $this->contextQuestionLibryoStore->syncContextQuestions($libryo, $contextQuestionIds);
        }

        $ids = Reference::forLibryoAutocompiled($libryo)->pluck('id')->toArray();

        if ($libryo->library) {
            $this->libraryReferenceStore->syncReferences($libryo->library, $ids);
        }

        return $ids;
    }

    /**
     * @param Libryo $libryo
     *
     * @return array<int, int>
     */
    public function getRefIdsForManual(Libryo $libryo): array
    {
        return (new Reference())->newQuery()
            ->inLibryoLibraries($libryo)
            ->forActiveWork()
            ->active()
            ->compilable()
            ->pluck('id')
            ->toArray();
    }

    /**
     * @param \App\Models\Customer\Libryo $libryo
     * @param array<int, int>             $ids
     *
     * @return void
     */
    protected function applyApplicabilityChanges(Libryo $libryo, array $ids): void
    {
        $exclude = Reference::forLibryoExcluded($libryo)->pluck('id')->all();
        $include = Reference::forLibryoIncluded($libryo)->pluck('id')->all();

        $ids = collect($ids)->filter(fn ($item) => !in_array($item, $exclude))->values()->all();

        $ids = [...$ids, ...$include];

        $this->libryoReferenceStore->syncLiveReferences($libryo, $ids);
        $this->libryoWorkStore->syncLiveWorksForReferences($libryo, $ids);
    }
}
