<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class GenericActivityUsingAuth extends UserActivityEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param UserActivityType  $type
     * @param array<mixed>|null $details
     */
    public function __construct(
        protected UserActivityType $type,
        protected ?array $details = null,
    ) {
        /** @var User $user */
        $user = Auth::user();
        /** @var ActiveNormasManager $manager */
        $manager = app(ActiveNormasManager::class);

        parent::__construct($user, $manager->getActive(), $manager->getActiveOrganisation());
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
