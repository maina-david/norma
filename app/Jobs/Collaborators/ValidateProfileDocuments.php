<?php

namespace App\Jobs\Collaborators;

use App\Enums\Collaborators\DocumentType;
use App\Mail\Collaborators\ProfileDocumentValidity;
use App\Models\Collaborators\ProfileDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ValidateProfileDocuments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $forCollaborator = [
            DocumentType::IDENTIFICATION,
            DocumentType::TRANSCRIPT,
            DocumentType::VISA,
            DocumentType::VITAE,
        ];

        ProfileDocument::whereHas('profile.collaborator', function ($query) {
            $query->where('active', true)
                ->whereHas('assignedTasks', function ($taskQuery) {
                    $taskQuery->where('updated_at', '>=', now()->subMonths(6));
                });
        })
            ->with(['profile.collaborator'])
            ->where('active', true)
            ->whereNotNull('approved_at')
            ->where(function ($builder) {
                $builder->whereDate('valid_to', now())->orWhereDate('valid_to', now()->addDays(7));
            })
            ->chunk(20, function ($documents) use ($forCollaborator) {
                $documents->each(function (ProfileDocument $doc) use ($forCollaborator) {
                    $mail = Mail::bcc(config('collaborate.emails.document_validation'));

                    if (in_array($doc->type, $forCollaborator)) {
                        $mail->to($doc->profile->collaborator);
                    }

                    $mail->send(new ProfileDocumentValidity($doc));

                    if ($doc->valid_to && now()->isSameDay($doc->valid_to)) {
                        $doc->update(['expired' => true]);
                    }
                });
            });
    }
}
