<?php

namespace App\View\Components\Tasks;

use App\Enums\Tasks\TaskStatus as TaskStatusEnum;
use App\Models\Tasks\Task;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskStatus extends Component
{
    public string $color = 'norma-gray-500';

    public string $title = '';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public int $status, public ?Task $task = null, public bool $changeable = false)
    {
        $this->color = match ($status) {
            TaskStatusEnum::notStarted()->value => 'negative',
            TaskStatusEnum::inProgress()->value => 'warning',
            TaskStatusEnum::done()->value => 'positive',
            TaskStatusEnum::paused()->value => 'norma-gray-500',
            // @codeCoverageIgnoreStart
            default => 'norma-gray-500',
            // @codeCoverageIgnoreEnd
        };

        /** @var string */
        $trans = TaskStatusEnum::lang()[$status] ?? '';
        $this->title = $trans;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.tasks.task-status');
    }
}
