<?php

namespace App\Mail\Notify;

class MonthlyNotification extends BaseLegalNotification
{
    /**
     * Get the subject of the email.
     *
     * @return string
     */
    protected function mailSubject(): string
    {
        /** @var string */
        return trans('mail.monthly_notification_subject', ['site' => $this->getAppName()]);
    }

    /**
     * Get the mail intro lines.
     *
     * @return array<int, string>
     */
    protected function introLines(): array
    {
        $fields = ['app-name' => $this->getAppName()];

        /** @var string $line1 */
        $line1 = trans('mail.monthly_notification_intro_line_1');
        /** @var string $line2 */
        $line2 = trans('mail.monthly_notification_intro_line_2', $fields);

        return [
            $line1,
            $line2,
        ];
    }
}
