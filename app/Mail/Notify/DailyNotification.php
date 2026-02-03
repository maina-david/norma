<?php

namespace App\Mail\Notify;

class DailyNotification extends BaseLegalNotification
{
    /**
     * Get the mail intro lines.
     *
     * @return array<int, string>
     */
    protected function introLines(): array
    {
        $fields = [
            'app-name' => $this->getAppName(),
            'anchor' => '<a href="mailto:info@norma.com">',
            'anchor-close' => '</a>',
            'bold' => '<span style="font-weight: bold">',
            'bold-close' => '</span>',
            'update-count' => $this->getUpdateCount(),
        ];

        /** @var string $line */
        $line = __('mail.daily_notification_intro_line_1', $fields);

        return [
            $line,
        ];
    }

    /**
     * Get the subject of the email.
     *
     * @return string
     */
    protected function mailSubject(): string
    {
        /** @var string */
        return __('mail.daily_notification_subject', ['site' => $this->getAppName()]);
    }
}
