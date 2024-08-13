<?php

namespace App\Mail\Compilation;

use App\Models\Customer\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContextBrief extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(protected Organisation $organisation, protected string $filePath, protected ?string $attachmentFilename = null)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->markdown('emails.compilation.context-brief')
            ->subject('Context Brief for ' . $this->organisation->title)
            ->with(['organisation' => $this->organisation])
            ->attach($this->filePath, [
                'as' => $this->attachmentFilename ?? 'ContextBrief.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}
