<?php

namespace App\Actions\Payments;

use App\Models\Collaborators\Collaborator;
use App\Models\Payments\Payment;
use App\Notifications\Payments\PaymentMade;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;

class HandlePaymentCreated
{
    use AsAction;

    public function handle(Payment $payment): void
    {
        /** @var Collection<Collaborator> */
        $users = Collaborator::whereHas('profile', function ($query) use ($payment) {
            $query->where('team_id', $payment->team_id)->admins();
        })->get();

        if ($users->count() === 0) {
            return;
        }

        Notification::send($users, (new PaymentMade($payment->id))->delay(now()->addMinutes(5)));
    }
}
