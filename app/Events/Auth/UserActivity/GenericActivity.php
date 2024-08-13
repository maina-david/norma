<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GenericActivity extends UserActivityEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param User                   $user
     * @param UserActivityType       $type
     * @param array<mixed>|null      $details
     * @param Libryo|null|null       $libryo
     * @param Organisation|null|null $organisation
     */
    public function __construct(
        User $user,
        protected UserActivityType $type,
        protected ?array $details = null,
        ?Libryo $libryo = null,
        ?Organisation $organisation = null,
    ) {
        parent::__construct($user, $libryo, $organisation);
    }

    /**
     * @param int $options
     *
     * @return string|false
     */
    public function toJson($options = 0): string|false
    {
        if (!$this->details) {
            return '{}';
        }

        return json_encode($this->details, $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return $this->type;
    }
}
