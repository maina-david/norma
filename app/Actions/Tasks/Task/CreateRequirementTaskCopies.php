<?php

namespace App\Actions\Tasks\Task;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Tasks\Task;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateRequirementTaskCopies
{
    use AsAction;

    /**
     * @param \Illuminate\Http\Request          $request
     * @param Task                              $task
     * @param \App\Models\Customer\Organisation $organisation
     * @param \App\Models\Auth\User             $user
     *
     * @return void
     */
    public function handle(Request $request, Task $task, Organisation $organisation, User $user): void
    {
        Norma::where('organisation_id', $organisation->id)
            ->whereRelation('references', 'id', $task->taskable_id)
            ->where('id', '!=', $task->place_id)
            ->userHasAccess($user)
            ->active()
            ->get()
            ->each(fn ($norma) => $this->copyTask($request, $task, $norma, $user));
    }

    /**
     * Create a copy of the task.
     *
     * @param \Illuminate\Http\Request    $request
     * @param \App\Models\Tasks\Task      $task
     * @param \App\Models\Customer\Norma $norma
     * @param \App\Models\Auth\User       $user
     *
     * @return void
     */
    protected function copyTask(Request $request, Task $task, Norma $norma, User $user): void
    {
        $copy = $task->replicate();
        $copy->place_id = $norma->id;
        $copy->source_task_id = $task->id;
        $copy->save();

        AfterTaskSaved::run($request, $task, $user);
    }
}
