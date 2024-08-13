<?php

namespace App\Actions\Payments;

use App\Models\Auth\User;
use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Generate a payment for a team's outstanding payment requests.
 */
class CreatePaymentForOutstanding
{
    use AsAction;

    public function handle(Team $team, Carbon $before, ?User $author = null): ?Payment
    {
        /** @var Collection<PaymentRequest> */
        $requests = $team->outstandingPaymentRequests()
            ->createdBefore($before)
            ->get();

        if ($requests->isEmpty()) {
            return null;
        }
        /** @var Payment */
        $payment = Payment::create([
            'team_id' => $team->id,
            'target_currency' => $team->currency,
        ]);

        $total = 0.0;
        foreach ($requests as $paymentRequest) {
            $amount = $paymentRequest->units * $paymentRequest->rate_per_unit;

            $paymentRequest->update([
                'payment_id' => $payment->id,
                'paid' => true,
                'paid_by_id' => $author?->id,
                'paid_at' => Carbon::now(),
                'amount_paid' => $amount,
                'currency_paid' => $team->currency,
            ]);
            $total += $amount;
        }

        $payment->update([
            'target_amount' => $total,
        ]);

        return $payment;
    }
}
