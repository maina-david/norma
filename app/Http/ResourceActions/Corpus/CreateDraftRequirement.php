<?php

namespace App\Http\ResourceActions\Corpus;

use App\Contracts\Http\ResourceAction;
use App\Enums\Requirements\RefRequirementChangeStatus;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Requirements\ReferenceRequirementDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CreateDraftRequirement implements ResourceAction
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
        return $user && $user->can('collaborate.corpus.reference.requirement.create');
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
        return __('corpus.reference.request_requirement');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'request-requirement';
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
        $referenceIds = Reference::where('work_id', $this->workId)
            ->typeCitation()
            ->whereKey($resourceIds)
            ->pluck('id')
            ->all();

        // TODO: requirements double write
        foreach (collect($referenceIds)->chunk(1000) as $refIds) {
            $inserts = [];
            foreach ($refIds as $refId) {
                $inserts[] = [
                    'reference_id' => $refId,
                    'change_status' => $request->get('removal', false) ? RefRequirementChangeStatus::REMOVE->value : RefRequirementChangeStatus::ADD->value,
                    'applied_by' => $request->user()->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ReferenceRequirementDraft::insertOrIgnore($inserts);
        }

        Session::flash('bulk-references', $resourceIds);

        Session::flash('flash.message', __('notifications.success'));

        return redirect()->route('collaborate.work-expressions.references.index', ['expression' => $this->expressionId]);
    }
}
