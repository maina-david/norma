<?php

namespace App\Http\ResourceActions\Corpus;

use App\Contracts\Http\ResourceAction;
use App\Enums\Corpus\ReferenceType;
use App\Models\Auth\User;
use App\Models\Corpus\ReferenceContentDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteReferenceContentDraft implements ResourceAction
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
        return $user && $user->can('collaborate.corpus.reference.delete');
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
        return __('corpus.reference.delete_draft_reference_content');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'delete-content-changes';
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
        ReferenceContentDraft::whereHas('reference', function ($query) use ($resourceIds) {
            $query->where('work_id', $this->workId)
                ->where('type', ReferenceType::citation()->value)
                ->whereKey($resourceIds);
        })->delete();

        return redirect()->back();
    }
}
