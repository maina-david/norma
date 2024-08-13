<?php

namespace App\Http\ResourceActions\Corpus;

use App\Actions\Corpus\Reference\RequestReferenceContentUpdate;
use App\Contracts\Http\ResourceAction;
use App\Enums\Corpus\ReferenceType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CreateReferenceContentDraft implements ResourceAction
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
        return __('corpus.reference.request_changes');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'request-content-changes';
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
        $user = auth()->user();

        Reference::where('work_id', $this->workId)
            ->where('type', ReferenceType::citation()->value)
            ->whereKey($resourceIds)
            ->with(['htmlContent', 'refPlainText'])
            ->select(['id'])
            ->chunk(2000, fn ($references) => $references->each(function ($ref) use ($user) {
                app(RequestReferenceContentUpdate::class)->handle($ref, $user);
            }));

        return redirect()->back();
    }
}
