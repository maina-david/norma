<?php

namespace App\Mail\Notify;

use App\Models\Customer\Norma;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NormaStreamDeactivated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Norma $norma)
    {
        $this->onQueue('notifications');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        /** @var string $subject */
        $subject = __('notify.norma_stream_deactivated.subject', ['stream' => $this->norma->title]);
        $this->norma->load(['legalDomains', 'location']);

        return $this->markdown('emails.notify.norma-stream-inactive')
            ->with(['norma' => $this->norma])
            ->subject($subject);
    }
}
