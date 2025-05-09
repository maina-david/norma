<?php

namespace App\Events\Auth\UserActivity\LegalUpdates;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;

class LegalUpdatesExported extends UserActivityEvent
{
    /**
     * @param string                                 $exportType
     * @param \App\Models\Auth\User                  $user
     * @param \App\Models\Customer\Norma|null       $norma
     * @param \App\Models\Customer\Organisation|null $organisation
     */
    public function __construct(protected string $exportType, User $user, ?Norma $norma = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $norma, $organisation);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::exportedLegalUpdate();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'type' => $this->exportType,
        ], $options);
    }
}
