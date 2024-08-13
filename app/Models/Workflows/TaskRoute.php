<?php

namespace App\Models\Workflows;

use App\Models\AbstractModel;
use App\Models\Auth\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * @mixin IdeHelperTaskRoute
 */
class TaskRoute extends AbstractModel
{
    protected $table = 'librarian_task_routes';

    /** @var array<string, string> */
    protected $casts = [
        'parameters' => 'array',
        'permission_parameters' => 'array',
    ];

    /**
     * Get the route for the given task.
     *
     * @param Task   $task
     * @param string $fallback
     *
     * @return string
     */
    public function generateRoute(Task $task, string $fallback = '#'): string
    {
        $task->load(['document.expression', 'document.legalUpdate']);

        $params = collect($this->parameters)
            ->map(fn ($val) => Arr::get($task, $val))
            ->toArray();

        $target = $this->route_name;

        /** @var User $user */
        $user = Auth::user();

        if ($this->permission) {
            $permissionParams = array_map(fn ($item) => Arr::get($task, $item), $this->permission_parameters ?? []);

            if ($user->cannot($this->permission, $permissionParams)) {
                $target = $this->fallback ?? $fallback;
            }
        }

        try {
            return route($target, $params);
        } catch (Throwable $t) {
            // @codeCoverageIgnoreStart
            return $fallback;
            // @codeCoverageIgnoreEnd
        }
    }
}
