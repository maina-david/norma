<?php

namespace App\Actions\Tasks\Task;

use App\Models\Auth\User;
use App\Models\Tasks\Task;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class HandleTaskCreating
{
    use AsAction;

    public function handle(Task $task): void
    {
        /** @var User|null */
        $user = Auth::user();
        if (!is_null($user)) {
            $task->author_id = $user->id;
        }
        // TODO: handle assessment item response creation when creating assessment task
    }
}
