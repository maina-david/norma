<?php

namespace App\Contracts\Workflows\TaskTrigger;

use App\Models\Workflows\TaskTrigger;

interface TriggerHandlerInterface
{
    /**
     * Handle the given trigger.
     *
     * @param TaskTrigger $trigger
     **/
    public function handle(TaskTrigger $trigger): void;
}
