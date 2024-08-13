<?php

namespace App\Mail\Notify;

use App\Models\Customer\Libryo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LibryoStreamDeactivated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Libryo $libryo)
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
        $subject = __('notify.libryo_stream_deactivated.subject', ['stream' => $this->libryo->title]);
        $this->libryo->load(['legalDomains', 'location']);

        return $this->markdown('emails.notify.libryo-stream-inactive')
            ->with(['libryo' => $this->libryo])
            ->subject($subject);
    }
}
