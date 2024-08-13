<?php

namespace App\Events\Workflows;

use App\Models\Workflows\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskTypeChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var Task */
    public Task $task;

    /**
     * Create a new event instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * @codeCoverageIgnore
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array<int, Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
