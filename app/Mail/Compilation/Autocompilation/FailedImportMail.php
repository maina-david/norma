<?php

namespace App\Mail\Compilation\Autocompilation;

use App\Models\Customer\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class FailedImportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param int    $organisationId
     * @param string $filePath
     */
    public function __construct(public int $organisationId, public string $filePath)
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (Storage::exists($this->filePath)) {
            $this->attachFromStorage($this->filePath, 'Applicability Import.xlsx');
        }

        /** @var Organisation $organisation */
        $organisation = Organisation::find($this->organisationId);

        /** @var string $subject */
        $subject = __('mail.failed_applicability_import', ['name' => $organisation->title]);

        return $this
            ->subject($subject)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->markdown('emails.compilation.autocompilation-failed', [
                'organisation' => $organisation,
            ]);
    }
}
