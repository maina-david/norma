<?php

namespace App\Actions\Tasks\Task;

use App\Enums\Tasks\TaskStatus;
use App\Models\Auth\User;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Just a temporary command that we will run when we change from the old codebase to the
 * monolith. Can be removed once monolith is launched.
 *
 * @codeCoverageIgnore
 */
class MigrateTaskSettings
{
    use AsAction;

    public string $commandSignature = 'libryo:migrate-task-settings';

    public function handle(): void
    {
        $cursor = User::whereJsonLength('settings->app_filters', '>', 0)->cursor();

        foreach ($cursor as $user) {
            $currentSettings = $user->settings['app_filters']['tasks'];

            $arr = [
                'statuses' => $currentSettings['status'] ?? [TaskStatus::notStarted()->value, TaskStatus::inProgress()->value],
                'type' => 'all',
            ];
            if (isset($currentSettings['assignee'])) {
                $arr['assignee'] = $currentSettings['assignee'];
            }

            $user->updateTaskFilters($arr);
        }
    }

    public function asJob(): void
    {
        $this->handle();
    }

    public function asCommand(): void
    {
        $this->handle();
    }
}
