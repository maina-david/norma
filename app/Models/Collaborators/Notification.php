<?php

namespace App\Models\Collaborators;

use Illuminate\Notifications\DatabaseNotification;

/**
 * @mixin IdeHelperNotification
 */
class Notification extends DatabaseNotification
{
    protected $table = 'librarian_notifications';
}
