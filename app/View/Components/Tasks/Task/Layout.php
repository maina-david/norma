<?php

namespace App\View\Components\Tasks\Task;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Layout extends Component
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
                'icon' => 'file-alt',
                'label' => __('tasks.task.views.list'),
                'route' => route('my.tasks.tasks.index'),
                'isCurrent' => $this->request->routeIs('my.tasks.tasks.index'),
            ],
            [
                'icon' => 'calendar',
                'label' => __('tasks.task.views.calendar'),
                'route' => route('my.tasks.tasks.calendar.index'),
                'isCurrent' => $this->request->routeIs('my.tasks.tasks.calendar.index'),
                'beta' => true,
            ],
        ];

        /** @var View */
        return view('partials.ui.plain-nav-layout', ['bordered' => true]);
    }
}
