<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAccessedPlatform extends UserActivityEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var Carbon/Carbon */
    protected Carbon $time;

    /** @var array<string,mixed> */
    protected array $extra;

    /**
     * @param User                $user
     * @param array<string,mixed> $extra
     */
    public function __construct(User $user, array $extra = [])
    {
        parent::__construct($user);
        $this->time = Carbon::now('UTC');
        $this->extra = $extra;
    }

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
        return json_encode(array_merge(
            $this->extra,
            [
                'user' => [
                    'id' => $this->user->id,
                ],
                'time' => $this->time->toDateTimeString(),
            ]
        ), $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::accessedPlatform();
    }
}
