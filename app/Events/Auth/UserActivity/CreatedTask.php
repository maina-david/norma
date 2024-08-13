<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Notify\LegalUpdate;
use App\Models\Storage\My\File;
use App\Models\Tasks\Task;

class CreatedTask extends UserActivityEvent
{
    /**
     * @param Task $task
     */
    public function __construct(
        protected Task $task
    ) {
        /** @var User */
        $author = $task->author;
        parent::__construct($author, $task->libryo);
    }

    /**
     * @return string|false
     **/
    public function toJson(int $options = 0): string|false
    {
        $arr = [
            'task_id' => $this->task->id,
        ];

        return json_encode($arr, $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        $taskable = $this->task->taskable;

        if ($taskable instanceof AssessmentItemResponse) {
            return UserActivityType::assessmentItemResponseTask();
        }
        if ($taskable instanceof File) {
            return UserActivityType::documentTask();
        }
        if ($taskable instanceof Libryo) {
            return UserActivityType::libryoTask();
        }
        if ($taskable instanceof Reference) {
            return UserActivityType::referenceTask();
        }
        if ($taskable instanceof LegalUpdate) {
            return UserActivityType::updatesTask();
        }

        return UserActivityType::libryoTask();
    }
}
