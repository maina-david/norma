<?php

namespace App\Mail\Auth\Onboarding;

use App\Mail\Auth\AbstractUserMailable;
use App\Models\Auth\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends AbstractUserMailable
{
    use Queueable;
    use SerializesModels;

    /** @var string */
    public string $token;

    /**
     * Create a new message instance.
     *
     * @param User   $user
     * @param string $token
     */
    public function __construct(User $user, string $token)
    {
        parent::__construct($user);
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        /** @var string $subject */
        $subject = __('mail.welcome_register');

        return $this->subject($subject)
            ->markdown('emails.auth.invitation-email')
            ->with([
                'fname' => $this->user->fname,
                'token' => $this->token,
                'appName' => $this->getAppName(),
            ]);
    }
}
