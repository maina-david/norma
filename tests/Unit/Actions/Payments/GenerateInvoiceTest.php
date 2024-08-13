<?php

namespace Tests\Unit\Actions\Payments;

use App\Actions\Payments\GenerateInvoice;
use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use App\Models\Payments\PaymentRequest;
use PDF;
use Tests\TestCase;

class GenerateInvoiceTest extends TestCase
{
    public function testHandle(): void
    {
        PDF::fake();
        $team = Team::factory()->create(['billing_address' => 'Simple Billing Address']);
        $payment = Payment::withoutEvents(function () use ($team) {
            return Payment::factory()->for($team)->create();
        });
        $paymentRequests = PaymentRequest::factory(3)->for($payment)->create();
        app(GenerateInvoice::class)->handle($payment);

        PDF::assertViewIs('pdf-templates.payments.collaborator-invoice');
        $addressLines = explode("\n", $payment->team->billing_address);
        PDF::assertSee($addressLines[0]);
        PDF::assertSee('VAT Registration No. 260148134');
        PDF::assertSee('Invoice No. ' . $payment->id);
    }
}
