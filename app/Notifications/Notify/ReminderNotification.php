<?php

namespace App\Notifications\Notify;

use App\Enums\Notifications\NotificationType;
use App\Mail\Notify\ReminderEmail;
use App\Models\Notify\Reminder;
use App\Notifications\AbstractNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Notifications\Notification;

class ReminderNotification extends AbstractNotification implements ShouldBeUnique
{
    use Queueable;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 21600;

    /**
     * Create a new notification instance.
     *
     * @param int      $userId
     * @param Reminder $reminder
     * @param mixed    $titleTranslationKey
     * @param mixed    $variables
     *
     * @return void
     */
    public function __construct(public int $userId, public Reminder $reminder, $titleTranslationKey, $variables = [])
    {
        parent::__construct(NotificationType::reminder()->value, $titleTranslationKey, $variables);
    }

    /**
     * The unique ID of the job.
     * We had a case where the job server had to be restarted, so multiple reminder jobs were created
     * for the same reminders. So our users got sent lots of emails.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function uniqueId()
    {
        return static::class . '_' . $this->reminder->id . '_' . $this->userId;
    }

    /**
     * Get the database format of the notification to be persisted.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $notifiable
     *
     * @return array<string, string|int|null>
     */
    public function databaseFormat($notifiable): array
    {
        return [
            'description' => $this->reminder->description,
            'reminder_id' => $this->reminder->id,
            'remindable_type' => $this->reminder->remindable_type,
            'remindable_id' => $this->reminder->remindable_id,
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $notifiable
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return $notifiable->shouldReceiveReminderEmail() ? ['mail', 'database'] : ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $notifiable
     *
     * @return ReminderEmail
     */
    public function toMail($notifiable): ReminderEmail
    {
        return new ReminderEmail($notifiable, $this->reminder);
    }
}
