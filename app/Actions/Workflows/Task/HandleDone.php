<?php

namespace App\Actions\Workflows\Task;

use App\Actions\Payments\PaymentRequest\CreateForTask;
use App\Models\Workflows\Task;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class HandleDone
{
    public function handle(Task $task): void
    {
        if ($task->auto_payment) {
            try {
                app(CreateForTask::class)->handle($task);
            } catch (UnprocessableEntityHttpException $ex) {
                //                Log::error('Auto payment for collaborate task failed: ' . $ex->getMessage());
            }
        }

        // You cannot use the parent task to close the children.
        //        if ($task->isParent()) {
        //            $this->closeChildTasks($task);
        //        }
    }

    //    private function closeChildTasks(Task $task): void
    //    {
    //        $task->childTasks->each(function ($child) {
    //            $child->update([
    //                'task_status' => TaskStatus::done()->value,
    //            ]);
    //        });
    //    }
}
