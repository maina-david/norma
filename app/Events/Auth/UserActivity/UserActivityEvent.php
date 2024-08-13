<?php

namespace App\Events\Auth\UserActivity;

use App\Contracts\Auth\UserActivityEventInterface;
use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class UserActivityEvent implements UserActivityEventInterface
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @var Libryo */
    public ?Libryo $libryo;

    /** @var Organisation */
    public ?Organisation $organisation;

    /** @var string */
    public $translationKey = 'activities.viewed_place';

    /**
     * @param User              $user
     * @param Libryo|null       $libryo
     * @param Organisation|null $organisation
     */
    public function __construct(
        public User $user,
        ?Libryo $libryo = null,
        ?Organisation $organisation = null
    ) {
        $this->libryo = $libryo;
        $this->organisation = $organisation;
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
     * Getter for user.
     *
     * @return User
     **/
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Getter for libryo.
     *
     * @return Libryo|null
     **/
    public function getLibryo(): ?Libryo
    {
        return $this->libryo;
    }

    /**
     * Getter for place.
     *
     * @return Organisation|null
     */
    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    /**
     * Get the activity type.
     *
     * @return UserActivityType
     **/
    abstract public function getActivityType(): UserActivityType;

    /**
     * @param int $options
     *
     * @return string|false
     **/
    abstract public function toJson(int $options = 0): string|false;
}
