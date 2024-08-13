<?php

namespace App\Actions\Workflows\TaskTransition;

use App\Actions\Payments\PaymentRequest\CreateForTask;
use App\Actions\Workflows\Task\HandleDone;
use App\Enums\Workflows\TaskStatus;
use App\Models\Workflows\Task;
use App\Models\Workflows\TaskTransition;
use App\Services\Workflows\TaskTriggerManager;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class HandleCreated
{
    use AsAction;

    public function __construct(protected HandleDone $handleDone)
    {
    }

    public function handle(TaskTransition $transition): void
    {
        /** @var Task|null */
        $task = (new Task())->newQueryWithoutScopes()->find($transition->task_id);
        if (!$task) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        if ($task->triggers->count() > 0) {
            $this->manageTriggers($transition);
        }

        if ($transition->transitioned_field === 'task_status') {
            $this->handleStatusChange($task, $transition);
        }
    }

    private function manageTriggers(TaskTransition $transition): void
    {
        app(TaskTriggerManager::class)->triggerFromTransition($transition);
    }

    private function handleStatusChange(Task $task, TaskTransition $transition): void
    {
        if ($transition->task_status === TaskStatus::done()->value) {
            $this->handleDone($task);
        }
    }

    private function handleDone(Task $task): void
    {
        if ($task->auto_payment) {
            try {
                app(CreateForTask::class)->handle($task);
            } catch (UnprocessableEntityHttpException $ex) {
                //                Log::error('Auto payment for collaborate task failed: ' . $ex->getMessage());
            }
        }
        if ($task->isParent()) {
            $this->handleDone->handle($task);
        }
    }
}
