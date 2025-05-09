<?php

namespace App\Events\Auth\UserActivity\LegalUpdates;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;

class LegalUpdateBookmarked extends UserActivityEvent
{
    /**
     * @param \App\Models\Notify\LegalUpdate         $update
     * @param \App\Models\Auth\User                  $user
     * @param \App\Models\Customer\Norma|null       $norma
     * @param \App\Models\Customer\Organisation|null $organisation
     */
    public function __construct(protected LegalUpdate $update, User $user, ?Norma $norma = null, ?Organisation $organisation = null)
    {
        parent::__construct($user, $norma, $organisation);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityType(): UserActivityType
    {
        return UserActivityType::bookmarkedLegalUpdate();
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(int $options = 0): string|false
    {
        return json_encode([
            'legal_update' => [
                'id' => $this->update->id,
            ],
        ], $options);
    }
}
