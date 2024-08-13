<?php

namespace App\View\Components\Workflows;

use App\Models\Workflows\Task;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskStatusSwitcher extends Component
{
    /**
     * Create a new component instance.
     *
     * @param Task $task
     */
    public function __construct(public Task $task)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render()
    {
        /** @var View */
        return view('components.workflows.task-status-switcher');
    }
}
