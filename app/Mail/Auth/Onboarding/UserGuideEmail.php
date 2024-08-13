<?php

namespace App\Mail\Auth\Onboarding;

use App\Mail\Auth\AbstractUserMailable;
use App\Models\Auth\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class UserGuideEmail extends AbstractUserMailable
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
        $genericUrl = 'https://cdn2.hubspot.net/hubfs/2566833/LIBRYO%20USER%20GUIDE_%202019.pdf';

        $hasWhitelabelGuide = [
            // 'dentons' => 'https://cdn2.hubspot.net/hubfs/2566833/User%20Guide%20-%20COVID-19-Dentons.pdf',
            // 'cleanchain' => 'https://cdn2.hubspot.net/hubfs/2566833/User%20Guide%20-%20COVID-19-CleanChain.pdf'
        ];

        // $userGuideUrl = !is_null($this->whitelabel) && isset($hasWhitelabelGuide[$this->whitelabel->shortname]) ? $hasWhitelabelGuide[$this->whitelabel->shortname] : $genericUrl;
        $userGuideUrl = $genericUrl;

        /** @var string $subject */
        $subject = __('mail.user_guide_subject');

        return $this->subject($subject)
            ->markdown('emails.auth.user-guide-email')
            ->with([
                'fname' => $this->user->fname,
                'appName' => $this->getAppName(),
                'userGuideUrl' => $userGuideUrl,
            ]);
    }
}
