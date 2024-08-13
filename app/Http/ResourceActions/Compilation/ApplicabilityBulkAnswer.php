<?php

namespace App\Http\ResourceActions\Compilation;

use App\Contracts\Http\ResourceAction;
use App\Http\ResourceActions\ResourceActionWithForm;
use App\Models\Auth\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicabilityBulkAnswer implements ResourceAction
{
    use ResourceActionWithForm;

    public function __construct(protected mixed $callback)
    {
    }

    /**
     * Check if the user is authorised to perform the action.
     *
     * @codeCoverageIgnore
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return true;
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('corpus.work.types.notice');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        /** @var string */
        return request('action');
    }

    /**
     * Get the path to the form view.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return string
     */
    protected function getFormView(Request $request, array $resourceIds): string
    {
        return 'partials.compilation.my.context-question.confirmation-modal';
    }

    /**
     * {@inheritDoc}
     */
    protected function submitLabel(): string
    {
        /** @var string */
        return __('actions.change');
    }

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse
     */
    public function handle(Request $request, array $resourceIds): RedirectResponse
    {
        if ($request->has('context_hide_duplicate_notice')) {
            /** @var User $user */
            $user = Auth::user();
            $user->updateSetting('context_hide_duplicate_notice', true);
        }

        /** @var RedirectResponse */
        return call_user_func($this->callback, $request);
    }
}
