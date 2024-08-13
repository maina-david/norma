<?php

namespace App\Observers\Workflows;

use App\Actions\Workflows\TaskTransition\HandleCreated;
use App\Models\Workflows\TaskTransition;

class TaskTransitionObserver
{
    /**
     * Handle the TaskTransition "created" event.
     *
     * @param \App\Models\Workflows\TaskTransition $taskTransition
     *
     * @return void
     */
    public function created(TaskTransition $taskTransition)
    {
        app(HandleCreated::class)->handle($taskTransition);
    }

    // /**
    //  * Handle the TaskTransition "updated" event.
    //  *
    //  * @param  \App\Models\Workflows\TaskTransition  $taskTransition
    //  * @return void
    //  */
    // public function updated(TaskTransition $taskTransition)
    // {
    //     //
    // }

    // /**
    //  * Handle the TaskTransition "deleted" event.
    //  *
    //  * @param  \App\Models\Workflows\TaskTransition  $taskTransition
    //  * @return void
    //  */
    // public function deleted(TaskTransition $taskTransition)
    // {
    //     //
    // }

    // /**
    //  * Handle the TaskTransition "restored" event.
    //  *
    //  * @param  \App\Models\Workflows\TaskTransition  $taskTransition
    //  * @return void
    //  */
    // public function restored(TaskTransition $taskTransition)
    // {
    //     //
    // }

    // /**
    //  * Handle the TaskTransition "force deleted" event.
    //  *
    //  * @param  \App\Models\Workflows\TaskTransition  $taskTransition
    //  * @return void
    //  */
    // public function forceDeleted(TaskTransition $taskTransition)
    // {
    //     //
    // }
}
