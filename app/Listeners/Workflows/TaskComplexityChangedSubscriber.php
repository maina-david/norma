<?php

namespace App\Listeners\Workflows;

use App\Events\Workflows\TaskComplexityChanged;
use App\Traits\Workflows\CreatesTransitions;

class TaskComplexityChangedSubscriber
{
    use CreatesTransitions;

    /**
     * Listen to the assignee changed event.
     *
     * @param TaskComplexityChanged $event
     *
     * @return void
     */
    public function handle(TaskComplexityChanged $event): void
    {
        $this->createTransition($event->task, 'complexity');
    }
}
