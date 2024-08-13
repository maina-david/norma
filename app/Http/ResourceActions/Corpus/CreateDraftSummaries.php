<?php

namespace App\Http\ResourceActions\Corpus;

use App\Contracts\Http\ResourceAction;
use App\Enums\Corpus\ReferenceType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Requirements\ReferenceSummaryDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CreateDraftSummaries implements ResourceAction
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
        return $user && $user->can('collaborate.requirements.summary.draft.create');
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
        return __('corpus.reference.request_summary_changes');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'request-summary-changes';
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
        Reference::where('work_id', $this->workId)
            ->where('type', ReferenceType::citation()->value)
//            ->has('summary')
            ->doesntHave('summaryDraft')
            ->whereKey($resourceIds)
            ->select(['id'])
            ->with(['summary'])
            ->chunk(2000, fn ($references) => $references->each(function ($ref) {
                ReferenceSummaryDraft::create([
                    'reference_id' => $ref->id,
                    'summary_body' => $ref->summary->summary_body ?? '',
                ]);
            }));

        Session::flash('bulk-references', $resourceIds);

        Session::flash('flash.message', __('corpus.reference.successfully_requested_changes'));

        return redirect()->route('collaborate.work-expressions.references.index', ['expression' => $this->expressionId]);
    }
}
