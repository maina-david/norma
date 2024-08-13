<?php

namespace App\Http\ResourceActions\Corpus;

use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use App\Models\Corpus\ReferenceContentExtract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeleteReferenceExtracts implements ResourceAction
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
        return $user && $user->can('collaborate.corpus.reference-content-extract.delete');
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
        return __('corpus.work_expression.delete_generated_extracts');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'delete-extracts';
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
        ReferenceContentExtract::whereIn('reference_id', $resourceIds)->delete();

        return redirect()->route('collaborate.work-expressions.references.index', ['expression' => $this->expressionId]);
    }
}
