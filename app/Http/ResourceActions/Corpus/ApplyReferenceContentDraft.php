<?php

namespace App\Http\ResourceActions\Corpus;

use App\Actions\Corpus\Reference\ApplyAllReferenceContentDrafts;
use App\Contracts\Http\ResourceAction;
use App\Models\Auth\User;
use App\Models\Corpus\Work;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplyReferenceContentDraft implements ResourceAction
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
        return $user && $user->can('collaborate.corpus.reference.request-update');
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
        return __('corpus.reference.apply_changes');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'apply-content-drafts';
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request         $request
     * @param array<int, int> $resourceIds
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse
    {
        $user = auth()->user();
        /** @var Work $work */
        $work = Work::findOrFail($this->workId);

        app(ApplyAllReferenceContentDrafts::class)->handle($work, $user, $resourceIds);

        return redirect()->back();
    }
}
