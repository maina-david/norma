<?php

namespace App\View\Components\Actions\Task;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class TaskLayout extends Component
{
    /** @var array<int, mixed> */
    public array $navItems = [];

    public function __construct(protected Request $request)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        $this->navItems = [
            [
                'label' => __('tasks.task.views.schedule'),
                'tooltip' => __('tasks.task.view_tooltips.schedule'),
                'icon' => 'user',
                'route' => route('my.actions.tasks.index', ['view' => 'schedule']),
                'isCurrent' => $this->request->route('view') === 'schedule',
            ],
            [
                'icon' => 'calendar',
                'label' => __('tasks.task.views.calendar'),
                'tooltip' => __('tasks.task.view_tooltips.calendar'),
                'route' => route('my.actions.tasks.index', ['view' => 'calendar']),
                'isCurrent' => $this->request->route('view') === 'calendar',
            ],

            [
                'label' => __('tasks.task.views.type'),
                'tooltip' => __('tasks.task.view_tooltips.type'),
                'icon' => 'font',
                'route' => route('my.actions.tasks.index', ['view' => 'type']),
                'isCurrent' => $this->request->route('view') === 'type',
            ],
            [
                'label' => __('tasks.task.views.topic'),
                'tooltip' => __('tasks.task.view_tooltips.topic'),
                'icon' => 'layer-group',
                'route' => route('my.actions.tasks.index', ['view' => 'topic']),
                'isCurrent' => $this->request->route('view') === 'topic',
            ],
            [
                'label' => __('tasks.task.views.due'),
                'tooltip' => __('tasks.task.view_tooltips.due'),
                'icon' => 'exclamation',
                'route' => route('my.actions.tasks.index', ['view' => 'due']),
                'isCurrent' => $this->request->route('view') === 'due',
            ],
            [
                'label' => __('tasks.task.views.status'),
                'tooltip' => __('tasks.task.view_tooltips.status'),
                'icon' => 'signal-bars',
                'route' => route('my.actions.tasks.index', ['view' => 'status']),
                'isCurrent' => $this->request->route('view') === 'status',
            ],
            [
                'label' => __('tasks.task.views.assignee'),
                'tooltip' => __('tasks.task.view_tooltips.assignee'),
                'icon' => 'users',
                'route' => route('my.actions.tasks.index', ['view' => 'assignee']),
                'isCurrent' => $this->request->route('view') === 'assignee',
            ],
            [
                'label' => __('tasks.task.views.control'),
                'tooltip' => __('tasks.task.view_tooltips.control'),
                'icon' => 'rotate-left',
                'route' => route('my.actions.tasks.index', ['view' => 'control']),
                'isCurrent' => $this->request->route('view') === 'control',
            ],
            [
                'icon' => 'file-alt',
                'label' => __('tasks.task.views.list'),
                'tooltip' => __('tasks.task.view_tooltips.list'),
                'route' => route('my.actions.tasks.index', ['view' => 'list']),
                'isCurrent' => $this->request->route('view') === 'list',
            ],
        ];

        /** @var View */
        return view('partials.ui.plain-nav-layout', ['bordered' => true]);
    }
}
