<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivatedNorma extends UserActivityEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var string */
    public $translationKey = 'activities.activated_norma';

    /**
     * @codeCoverageIgnore
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array<int,Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    /**
     * @param int $options
     *
     * @return string|false
     **/
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'user' => [
                'id' => $this->user->id,
            ],
            'norma' => [
                'id' => $this->norma->id ?? null,
            ],
        ], $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::normaActivate();
    }
}
