<?php

namespace App\Events\Auth\UserActivity;

use App\Enums\Auth\UserActivityType;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FilteredResource extends UserActivityEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param User                 $user
     * @param UserActivityType     $type
     * @param array<string, mixed> $filters
     * @param Norma|null          $norma
     * @param Organisation|null    $organisation
     */
    public function __construct(
        User $user,
        protected UserActivityType $type,
        protected array $filters,
        ?Norma $norma = null,
        ?Organisation $organisation = null,
    ) {
        parent::__construct($user, $norma, $organisation);
    }

    /**
     * @param int $options
     *
     * @return string|false
     */
    public function toJson($options = 0): string|false
    {
        return json_encode($this->filters, $options);
    }

    /**
     * @return UserActivityType
     **/
    public function getActivityType(): UserActivityType
    {
        return $this->type;
    }
}
