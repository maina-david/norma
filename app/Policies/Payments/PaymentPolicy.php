<?php

namespace App\Policies\Payments;

use App\Models\Auth\User;
use App\Models\Collaborators\Profile;
use App\Models\Payments\Payment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Payment $payment): bool
    {
        if ($user->can('collaborate.payments.payment.viewAny')) {
            return true;
        }

        return Profile::where('user_id', $user->id)
            ->where('team_id', $payment->team_id)
            ->where('is_team_admin', true)
            ->exists();
    }

    public function downloadInvoice(User $user, Payment $payment): bool
    {
        return $this->view($user, $payment);
    }
}
