<?php

namespace App\Http\ResourceActions\Corpus;

use App\Actions\Corpus\Reference\BulkApplyTaggableDrafts;
use App\Contracts\Http\ResourceAction;
use App\Http\Controllers\Traits\UsesMetaDataPivotClasses;
use App\Models\Corpus\Reference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

abstract class ApplyDraftTaggables implements ResourceAction
{
    use UsesMetaDataPivotClasses;

    /**
     * @param int $workId
     * @param int $expressionId
     */
    public function __construct(protected int $workId, protected int $expressionId)
    {
    }

    /**
     * @return string
     */
    abstract protected function getTaggableRelation(): string;

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
        return __('corpus.reference.apply_drafts');
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
        $refIds = Reference::where('work_id', $this->workId)
            ->typeCitation()
            ->whereKey($resourceIds)
            ->pluck('id')
            ->toArray();

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        app(BulkApplyTaggableDrafts::class)->handle(
            $refIds,
            $this->getMetaPivotClass($this->getTaggableRelation()),
            $this->getMetaDraftPivotClass($this->getTaggableRelation()),
            $user,
        );

        Session::flash('bulk-references', $resourceIds);

        Session::flash('flash.message', __('corpus.reference.successfully_applied_drafts'));

        return redirect()->route('collaborate.work-expressions.references.index', ['expression' => $this->expressionId]);
    }
}
