<?php

namespace App\View\Components\Collaborators;

use App\Models\Collaborators\Notification;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NotificationText extends Component
{
    /**
     * Create a new component instance.
     *
     * @param Notification $notification
     *
     * @return void
     */
    public function __construct(protected $notification)
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
        return view('components.collaborators.notification-text');
    }

    /**
     * @return array<string>
     */
    public function getLines(): array
    {
        return $this->notification->data['lines'] ?? [];
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->notification->data['message'] ?? '';
    }

    /**
     * @return string
     */
    public function getTaskLink(): string
    {
        if (!isset($this->notification->data['task_id'])) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }

        return route('collaborate.tasks.show', ['task' => $this->notification->data['task_id']]);
    }
}
