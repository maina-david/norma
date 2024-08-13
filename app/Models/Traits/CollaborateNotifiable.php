<?php

namespace App\Models\Traits;

use App\Models\Collaborators\Notification;
use Illuminate\Notifications\RoutesNotifications;

trait CollaborateNotifiable
{
    use RoutesNotifications;

    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    /**
     * Get the entity's read notifications.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function readNotifications()
    {
        /** @var \Illuminate\Database\Query\Builder */
        return $this->notifications()->whereNotNull('read_at');
    }

    /**
     * Get the entity's unread notifications.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function unreadNotifications()
    {
        /** @var \Illuminate\Database\Query\Builder */
        return $this->notifications()->whereNull('read_at');
    }
}
