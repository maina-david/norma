<?php

namespace App\Actions\Payments;

use App\Models\Payments\Payment;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateInvoice
{
    use AsAction;

    public function handle(Payment $payment): PdfWrapper
    {
        $payment->load([
            'team',
            'paymentRequests.task' => fn ($q) => $q->withoutGlobalScopes(),
        ]);

        $invoiceDate = Carbon::parse($payment->created_at)->subDay();

        return SnappyPdf::loadView('pdf-templates.payments.collaborator-invoice', ['payment' => $payment, 'invoiceDate' => $invoiceDate]);
    }
}
