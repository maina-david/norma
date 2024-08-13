<?php

namespace App\Notifications\Tasks;

use App\Notifications\AbstractNotification;

abstract class AbstractTaskNotification extends AbstractNotification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }
}
