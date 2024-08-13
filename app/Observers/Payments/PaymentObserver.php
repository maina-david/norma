<?php

namespace App\Observers\Payments;

use App\Actions\Payments\HandlePaymentCreated;
use App\Models\Payments\Payment;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     *
     * @param \App\Models\Payments\Payment $payment
     *
     * @return void
     */
    public function created(Payment $payment)
    {
        app(HandlePaymentCreated::class)->handle($payment);
    }

    // /**
    //  * Handle the Payment "updated" event.
    //  *
    //  * @param  \App\Models\Payments\Payment  $payment
    //  * @return void
    //  */
    // public function updated(Payment $payment)
    // {
    //     //
    // }

    // /**
    //  * Handle the Payment "deleted" event.
    //  *
    //  * @param  \App\Models\Payments\Payment  $payment
    //  * @return void
    //  */
    // public function deleted(Payment $payment)
    // {
    //     //
    // }

    // /**
    //  * Handle the Payment "restored" event.
    //  *
    //  * @param  \App\Models\Payments\Payment  $payment
    //  * @return void
    //  */
    // public function restored(Payment $payment)
    // {
    //     //
    // }

    // /**
    //  * Handle the Payment "force deleted" event.
    //  *
    //  * @param  \App\Models\Payments\Payment  $payment
    //  * @return void
    //  */
    // public function forceDeleted(Payment $payment)
    // {
    //     //
    // }
}
