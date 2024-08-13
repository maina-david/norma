<?php

namespace App\View\Components\Tasks\TaskActivity;

use App\Enums\Tasks\TaskActivityType;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActivityIcon extends Component
{
    /** @var string */
    public string $icon = 'exchange';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public int $activityType)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $this->icon = match ($this->activityType) {
            TaskActivityType::statusChange()->value => 'exchange',
            TaskActivityType::madeComment()->value => 'comment-lines',
            TaskActivityType::documentUpload()->value => 'cloud-upload',
            TaskActivityType::assigneeChange()->value => 'user-edit',
            TaskActivityType::watchTask()->value => 'eye',
            TaskActivityType::dueDateChanged()->value => 'calendar-edit',
            TaskActivityType::setReminder()->value => 'alarm-clock',
            TaskActivityType::priorityChanged()->value => 'thermometer-half',
            TaskActivityType::titleChanged()->value => 'pencil',
            // @codeCoverageIgnoreStart
            default => 'exchange',
            // @codeCoverageIgnoreEnd
        };

        /** @var View */
        return view('components.tasks.task-activity.activity-icon');
    }
}
