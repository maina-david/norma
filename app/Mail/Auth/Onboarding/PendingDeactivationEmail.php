<?php

namespace App\Mail\Auth\Onboarding;

use App\Mail\Auth\AbstractUserMailable;
use App\Models\Auth\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class PendingDeactivationEmail extends AbstractUserMailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        parent::__construct($user);
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /** @var string $subject */
        $subject = __('mail.pending_deactivation_subject', ['name' => $this->user->fname]);

        return $this->subject($subject)
            ->markdown('emails.auth.pending-deactivation-email')
            ->with([
                'fname' => $this->user->fname,
                'appName' => $this->getAppName(),
            ]);
    }
}
