<?php

namespace App\Notifications;

use App\Enums\Notifications\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class AbstractNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param int                  $notificationType
     * @param string               $titleTranslationKey
     * @param array<string, mixed> $variables
     */
    public function __construct(
        protected int $notificationType,
        protected string $titleTranslationKey,
        protected array $variables = []
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $notifiable
     *
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return MailMessage
     */
    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @codeCoverageIgnore
     *
     * @param mixed $notifiable
     *
     * @return array<mixed>
     */
    public function toArray($notifiable)
    {
        return [];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable)
    {
        $body = $this->databaseFormat($notifiable);
        $body['type'] = property_exists($this, 'type') && $this->type instanceof NotificationType ? $this->type->value : $this->notificationType;

        return [
            'title' => [
                'translation_key' => $this->titleTranslationKey,
                'variables' => $this->variables,
            ],
            'body' => $body,
        ];
    }

    /**
     * Get the database format of the notification to be persisted.
     *
     * @param mixed $notifiable
     *
     * @return array<mixed>
     */
    abstract public function databaseFormat($notifiable);
}
