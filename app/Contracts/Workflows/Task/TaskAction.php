<?php

namespace App\Contracts\Workflows\Task;

use App\Models\Workflows\Task;

interface TaskAction
{
    /**
     * Execute the task action.
     *
     * @param Task       $task
     * @param mixed|null $payload
     *
     * @return void
     */
    public function handle(Task $task, mixed $payload = null): void;
}
