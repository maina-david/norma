<?php

namespace App\Mail\Corpus;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IngestionMissingActionAreas extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array<mixed> $missingAreas
     */
    public function __construct(protected array $missingAreas)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Missing action areas in your import')
            ->markdown('emails.corpus.ingestion-missing-action-areas', [
                'missingAreas' => $this->missingAreas,
            ]);
    }
}
