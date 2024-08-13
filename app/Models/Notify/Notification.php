<?php

namespace App\Models\Notify;

use App\Enums\Notifications\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @mixin IdeHelperNotification
 */
class Notification extends DatabaseNotification
{
    use HasFactory;

    /** @var array<string, mixed> */
    public array $presentViewData;

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return bool
     */
    public function isForTask(): bool
    {
        $type = (int) ($this->data['body']['type'] ?? 0);

        return in_array($type, [
            NotificationType::taskAssigned()->value,
            NotificationType::taskAssigneeChanged()->value,
            NotificationType::taskDueDateChanged()->value,
            NotificationType::taskStatusChanged()->value,
            NotificationType::taskDue()->value,
            NotificationType::taskPriorityChanged()->value,
            NotificationType::taskCompleted()->value,
            NotificationType::taskTitleChanged()->value,
        ]);
    }

    /**
     * @return bool
     */
    public function isForReminder(): bool
    {
        $type = (int) ($this->data['body']['type'] ?? 0);

        return in_array($type, [
            NotificationType::reminder()->value,
        ]);
    }

    /**
     * @return bool
     */
    public function isForComment(): bool
    {
        $type = (int) ($this->data['body']['type'] ?? 0);

        return in_array($type, [
            NotificationType::mention()->value,
            NotificationType::reply()->value,
        ]);
    }

    /**
     * @return bool
     */
    public function isTypeMention(): bool
    {
        $type = (int) ($this->data['body']['type'] ?? 0);

        return NotificationType::mention()->is($type);
    }

    /**
     * @return bool
     */
    public function isTypeTaskDue(): bool
    {
        $type = (int) ($this->data['body']['type'] ?? 0);

        return NotificationType::taskDue()->is($type);
    }
}
