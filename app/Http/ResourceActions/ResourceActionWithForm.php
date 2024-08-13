<?php

namespace App\Http\ResourceActions;

use HotwiredLaravel\TurboLaravel\Http\MultiplePendingTurboStreamResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

trait ResourceActionWithForm
{
    /**
     * Get the path to the form view.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return string
     */
    abstract protected function getFormView(Request $request, array $resourceIds): string;

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    abstract public function label(): string;

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    abstract public function actionId(): string;

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse
     */
    abstract public function handle(Request $request, array $resourceIds): RedirectResponse;

    /**
     * Handle the action after it has been triggered.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return RedirectResponse|MultiplePendingTurboStreamResponse
     */
    public function trigger(Request $request, array $resourceIds): RedirectResponse|MultiplePendingTurboStreamResponse
    {
        if ($this->formIsValid($request)) {
            return $this->handle($request, $resourceIds);
        }

        return $this->generateForm($request, $resourceIds);
    }

    /**
     * Check if the form is valid.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function formIsValid(Request $request): bool
    {
        return Hash::check('1', $request->get('action-validated', ''));
    }

    /**
     * Label to use on the form submit button.
     *
     * @return string
     */
    protected function submitLabel(): string
    {
        /** @var string */
        return __('actions.finish');
    }

    /**
     * Get the form to be sent back to the user for viewing.
     *
     * @param Request                $request
     * @param array<int, int|string> $resourceIds
     *
     * @return MultiplePendingTurboStreamResponse
     */
    protected function generateForm(Request $request, array $resourceIds): MultiplePendingTurboStreamResponse
    {
        return multipleTurboStreamResponse([
            singleTurboStreamResponse('actions-modal-target', 'replace')
                ->view('partials.ui.resource-actions-modal', [
                    'action' => $this->actionId(),
                    'label' => $this->label(),
                    'resources' => $resourceIds,
                    'route' => $request->url(),
                    'view' => $this->getFormView($request, $resourceIds),
                    'submitLabel' => $this->submitLabel(),
                ]),
        ]);
    }
}
