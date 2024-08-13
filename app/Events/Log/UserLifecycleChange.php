<?php

namespace App\Events\Log;

use App\Models\Auth\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLifecycleChange
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param User $user
     * @param int  $from_lifecycle
     * @param int  $to_lifecycle
     */
    public function __construct(
        public User $user,
        protected int $from_lifecycle,
        protected int $to_lifecycle
    ) {
    }

    /**
     * @codeCoverageIgnore
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array<\Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    /**
     * Getter for user.
     *
     * @return User
     **/
    public function getUser()
    {
        return $this->user;
    }

    public function getFromLifecycle(): int
    {
        return $this->from_lifecycle;
    }

    public function getToLifecycle(): int
    {
        return $this->to_lifecycle;
    }
}
