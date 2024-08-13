<?php

namespace App\Http\ResourceActions\Corpus;

use App\Contracts\Http\ResourceAction;
use App\Enums\Requirements\RefRequirementChangeStatus;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Requirements\ReferenceRequirement;
use App\Models\Requirements\ReferenceRequirementDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ApplyDraftRequirement implements ResourceAction
{
    /**
     * @param int $workId
     * @param int $expressionId
     */
    public function __construct(protected int $workId, protected int $expressionId)
    {
    }

    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.corpus.reference.requirement.apply');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('corpus.reference.apply_requirement_drafts');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'apply-requirement-drafts';
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse
    {
        $now = now()->format('Y-m-d H:i:s');

        // TODO: requirements double write
        Reference::whereKey($resourceIds)
            ->where('work_id', $this->workId)
            ->typeCitation()
            ->has('requirementDraft')
            ->with(['requirementDraft'])
            ->chunk(1000, function ($references) use ($request, $now) {
                $inserts = [];
                $deletions = [];
                $draftDeletions = [];
                foreach ($references as $reference) {
                    /** @var \App\Models\Requirements\ReferenceRequirementDraft $draft */
                    $draft = $reference->requirementDraft;
                    if ($draft->change_status === RefRequirementChangeStatus::ADD->value) {
                        $inserts[] = [
                            'reference_id' => $reference->id,
                            'applied_by' => $draft->applied_by,
                            'approved_by' => $request->user()->id ?? null,
                            'annotation_source_id' => $draft->annotation_source_id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    } else {
                        // @codeCoverageIgnoreStart
                        $deletions[] = $reference->id;
                        // @codeCoverageIgnoreEnd
                    }
                    $draftDeletions[] = $reference->id;
                }

                if (!empty($inserts)) {
                    ReferenceRequirement::insertOrIgnore($inserts);
                }
                if (!empty($deletions)) {
                    // @codeCoverageIgnoreStart
                    // TODO: requirements double write - this is not being tested yet, as it's not used...
                    // remove the code coverage ignore once tested
                    ReferenceRequirement::whereIn('reference_id', $deletions)
                        ->delete();
                    // @codeCoverageIgnoreEnd
                }

                if (!empty($draftDeletions)) {
                    ReferenceRequirementDraft::whereIn('reference_id', $draftDeletions)
                        ->delete();
                }
            });

        Session::flash('bulk-references', $resourceIds);

        Session::flash('flash.message', __('corpus.reference.successfully_applied_drafts'));

        return redirect()->route('collaborate.work-expressions.references.index', ['expression' => $this->expressionId]);
    }
}
