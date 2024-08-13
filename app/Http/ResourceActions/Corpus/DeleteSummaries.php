<?php

namespace App\Http\ResourceActions\Corpus;

use App\Contracts\Http\ResourceAction;
use App\Enums\Corpus\ReferenceType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DeleteSummaries implements ResourceAction
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
        return $user && $user->can('collaborate.requirements.summary.delete');
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
        return __('corpus.reference.delete_summaries');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'delete-summaries';
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
            ->has('summary')
            ->whereKey($resourceIds)
            ->select(['id'])
            ->with(['summary', 'summaryDraft'])
            ->chunk(2000, fn ($references) => $references->each(function ($ref) {
                $ref->summary?->delete();
                $ref->summaryDraft?->delete();
            }));

        Session::flash('bulk-references', $resourceIds);

        Session::flash('flash.message', __('corpus.reference.successfully_deleted'));

        return redirect()->route('collaborate.work-expressions.references.index', ['expression' => $this->expressionId]);
    }
}
