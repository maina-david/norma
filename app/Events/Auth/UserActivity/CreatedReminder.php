<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Notify\Reminder;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;

class CreatedReminder extends UserActivityEvent
{
    /**
     * @param User $user
     * @param int  $reminderId
     */
    public function __construct(
        User $user,
        protected int $reminderId
    ) {
        parent::__construct($user);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int $options
     *
     * @return string|false
     */
    public function toJson($options = 0): string|false
    {
        return json_encode([
            'reminder_id' => $this->reminderId,
        ], $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        /** @var Reminder */
        $reminder = Reminder::find($this->reminderId);
        $remindable = $reminder->remindable;

        if ($remindable instanceof Reference) {
            return UserActivityType::referenceReminder();
        }
        if ($remindable instanceof File) {
            return UserActivityType::documentReminder();
        }
        if ($remindable instanceof Task) {
            return UserActivityType::taskReminder();
        }

        // @codeCoverageIgnoreStart
        return UserActivityType::documentReminder();
        // @codeCoverageIgnoreEnd
    }
}
