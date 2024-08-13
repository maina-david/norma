<?php

namespace App\Actions\Payments\PaymentRequest;

use App\Models\Payments\PaymentRequest;
use App\Models\Workflows\Task;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CreateForTask
{
    use AsAction;

    public function handle(Task $task): ?PaymentRequest
    {
        if (is_null($task->assignee)) {
            /** @var string */
            $msg = __('workflows.task.errors.task_not_assigned');
            throw new UnprocessableEntityHttpException($msg);
        }

        if ($task->paymentRequests->count() > 0) {
            /** @var string */
            $msg = __('workflows.task.errors.payment_request_exists');
            throw new UnprocessableEntityHttpException($msg);
        }

        $rate = $task->assignee->profile->team->getRateForTask($task) ?? null;

        if (is_null($task->assignee->profile->team->currency)) {
            /** @var string */
            $msg = __('workflows.task.errors.no_currency_set');
            throw new UnprocessableEntityHttpException($msg);
        }

        if (is_null($rate)) {
            /** @var string */
            $msg = __('workflows.task.errors.no_rate');
            throw new UnprocessableEntityHttpException($msg);
        }

        $toPay = ($task->units ?? 0) * $rate->rate_per_unit;

        if ($toPay == 0.0) {
            /** @var string */
            $msg = __('workflows.task.errors.no_units');
            throw new UnprocessableEntityHttpException($msg);
        }

        /** @var PaymentRequest */
        return PaymentRequest::create([
            'team_id' => $task->assignee->profile->team_id,
            'task_id' => $task->id,
            'units' => $task->units,
            'rate_per_unit' => $rate->rate_per_unit,
            'amount_paid' => $toPay,
            'currency_paid' => $task->assignee->profile->team->currency,
        ]);
    }
}
