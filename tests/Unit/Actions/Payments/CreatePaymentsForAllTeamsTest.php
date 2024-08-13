<?php

namespace Tests\Unit\Actions\Payments;

use App\Actions\Payments\CreatePaymentsForAllTeams;
use App\Models\Collaborators\Team;
use App\Models\Payments\PaymentRequest;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CreatePaymentsForAllTeamsTest extends TestCase
{
    public function testHandle(): void
    {
        Mail::fake();
        $teams = Team::factory(2)->create();
        $this->travelTo(now()->startOfMonth());
        $paymentRequest1 = PaymentRequest::factory()->for($teams[0])->create();
        $this->travelBack();
        $this->travelTo(now()->startOfMonth()->subDays(5));
        $this->assertTrue($teams[0]->payments->isEmpty());
        $paymentRequest2 = PaymentRequest::factory()->for($teams[0])->create();
        $paymentRequest3 = PaymentRequest::factory()->for($teams[1])->create();
        $this->travelBack();
        $this->travelTo(now()->startOfMonth());

        app(CreatePaymentsForAllTeams::class)->handle();

        $this->assertTrue($teams[0]->refresh()->payments->isNotEmpty());
        $this->assertTrue($teams[1]->refresh()->payments->isNotEmpty());
        // this payment request was made this month... should not be included
        $this->assertNull($paymentRequest1->refresh()->payment_id);
        // these were made the previous month, so should be included
        $this->assertNotNull($paymentRequest2->refresh()->payment_id);
        $this->assertNotNull($paymentRequest3->refresh()->payment_id);
    }
}
