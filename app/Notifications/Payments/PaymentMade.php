<?php

namespace App\Notifications\Payments;

use App\Actions\Payments\GenerateInvoice;
use App\Models\Payments\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class PaymentMade extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(protected int $paymentId)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array<string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        /** @var Payment */
        $payment = Payment::findOrFail($this->paymentId);
        $invoicePDF = app(GenerateInvoice::class)->handle($payment);
        $invoiceDate = Carbon::parse($payment->created_at)->subDay();
        $title = sprintf('%s Invoice', $invoiceDate->format('F Y'));

        return (new MailMessage())
            ->markdown('emails.payments.payment-made', ['user' => $notifiable])
            ->subject('Norma Collaborate Payment')
            ->cc(config('collaborate.emails.collaborator_invoice_cc'))
            ->bcc(config('collaborate.emails.collaborator_invoice_bcc'))
            ->attachData($invoicePDF->output(), $title . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $notifiable
     *
     * @return array<mixed>
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
