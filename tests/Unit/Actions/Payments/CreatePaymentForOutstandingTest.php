<?php

namespace Tests\Unit\Actions\Payments;

use App\Actions\Payments\CreatePaymentForOutstanding;
use App\Models\Auth\User;
use App\Models\Collaborators\Team;
use App\Models\Payments\PaymentRequest;
use Tests\TestCase;

class CreatePaymentForOutstandingTest extends TestCase
{
    public function testHandle(): void
    {
        $team = Team::factory()->create();
        $payment = app(CreatePaymentForOutstanding::class)->handle($team, now());
        $this->assertNull($payment);

        $this->travel(-5)->days();
        $paymentRequests = PaymentRequest::factory(2)->for($team)->create();
        $this->travelBack();

        $user = User::factory()->create();
        app(CreatePaymentForOutstanding::class)->handle($team, now(), $user);
        $paymentRequests = PaymentRequest::with(['payment'])
            ->find($paymentRequests->modelKeys());
        $total = 0.0;
        foreach ($paymentRequests as $request) {
            $total += $request->units * $request->rate_per_unit;
            $this->assertNotNull($request->payment_id);
            $this->assertTrue($request->paid);
            $this->assertNotNull($request->paid_at);
            $this->assertSame($user->id, $request->paid_by_id);
            $this->assertSame($team->currency, $request->currency_paid);
            $this->assertSame(round($request->units * $request->rate_per_unit, 2), round($request->amount_paid, 2));
        }

        $this->assertSame(round($total, 2), $paymentRequests->first()->payment->target_amount);
    }
}
