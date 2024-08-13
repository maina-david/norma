<?php

namespace App\Http\ResourceActions\Workflows;

use App\Actions\Payments\PaymentRequest\CreateForTask;
use App\Contracts\Http\ResourceAction;
use App\Enums\Workflows\TaskStatus;
use App\Models\Auth\User;
use App\Models\Workflows\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RequestPayment implements ResourceAction
{
    /**
     * Check if the user is authorised to perform the action.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function authorise(?User $user): bool
    {
        return $user && $user->can('collaborate.payments.payment-request.create');
    }

    /**
     * Get the label of the action that will be displayed to the user.
     *
     * @return string
     */
    public function label(): string
    {
        /** @var string */
        return __('workflows.task.request_payment');
    }

    /**
     * Get the unique identifier of the title that will be used to trigger it.
     *
     * @return string
     */
    public function actionId(): string
    {
        return 'request-payment';
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
        Task::whereKey($resourceIds)
            ->with(['assignee.profile.team', 'paymentRequests'])
            ->whereIn('task_status', [TaskStatus::inReview()->value, TaskStatus::done()->value])
            ->doesntHave('paymentRequests')
            ->whereHas('assignee.profile.team', fn ($query) => $query->whereNotNull('currency'))
            ->whereHas('assignee.profile.team.rates', function ($query) {
                $query->whereColumn('for_task_type_id', 'task_type_id');
            })
            ->each(fn (Task $task) => app(CreateForTask::class)->handle($task));

        Session::flash('flash.message', __('workflows.task.successfully_requested_payment'));

        return back();
    }
}
