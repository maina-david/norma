<?php

namespace App\Events\Auth\UserActivity\AssessmentItems;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;

class FilteredAssessmentItems extends UserActivityEvent
{
    /**
     * @param array<string, mixed>                   $filters
     * @param \App\Models\Auth\User                  $user
     * @param \App\Models\Customer\Norma|null       $norma
     * @param \App\Models\Customer\Organisation|null $organisation
     */
    public function __construct(protected array $filters, User $user, ?Norma $norma = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $norma, $organisation);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::filteredAssessmentItems();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'filters' => $this->filters,
        ], $options);
    }
}
