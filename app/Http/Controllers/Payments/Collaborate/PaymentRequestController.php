<?php

namespace App\Http\Controllers\Payments\Collaborate;

use App\Actions\Payments\PaymentRequest\CreateForTask;
use App\Http\Controllers\Controller;
use App\Models\Payments\PaymentRequest;
use App\Models\Workflows\Task;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PaymentRequestController extends Controller
{
    public function destroy(PaymentRequest $paymentRequest): RedirectResponse
    {
        $paymentRequest->delete();
        $this->notifyGeneralSuccess();

        return back();
    }

    public function storeForTask(Task $task): RedirectResponse
    {
        try {
            app(CreateForTask::class)->handle($task);
            $this->notifyGeneralSuccess();
        } catch (UnprocessableEntityHttpException $ex) {
            $this->notifyErrorMessage($ex->getMessage());
        }

        return back();
    }
}
