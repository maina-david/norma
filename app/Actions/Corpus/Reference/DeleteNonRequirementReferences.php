<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteNonRequirementReferences
{
    use AsAction;

    /**
     * @param Work $work
     *
     * @return void
     */
    public function handle(Work $work): void
    {
        $chunks = Reference::typeCitation()
            ->where('work_id', $work->id)
            ->doesntHave('requirementDraft')
            ->doesntHave('refRequirement')
            ->doesntHave('collaborateComments')
            ->doesntHave('contextQuestions')
            ->doesntHave('contextQuestionDrafts')
            ->doesntHave('assessmentItems')
            ->doesntHave('assessmentItemDrafts')
            ->doesntHave('categories')
            ->doesntHave('categoryDrafts')
            ->doesntHave('linkedParents')
            ->doesntHave('linkedChildren')
            ->pluck('id')
            ->chunk(1000);

        foreach ($chunks as $chunk) {
            Reference::whereKey($chunk->all())->update(['deleted_at' => now()]);
        }

        app(MigrateReferencesToAugModel::class)->handle($work);
    }
}
