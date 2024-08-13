<?php

namespace App\Enums\Auth;

use App\Enums\Enum;

/**
 * @property int $value;
 *
 * @method static LifecycleStage notOnboarded()
 * @method static LifecycleStage invitationSent()
 * @method static LifecycleStage invitationDeclined()
 * @method static LifecycleStage deactivated()
 * @method static LifecycleStage deactivatedInactivity()
 * @method static LifecycleStage active()
 */
class LifecycleStage extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'auth.user.lifecycle_stage_';

    /**
     * Get the allowed enums.
     *
     * @return array<string,int>
     */
    protected static function enums(): array
    {
        return [
            'notOnboarded' => 0,
            'invitationSent' => 1,
            'invitationDeclined' => 2,
            'deactivated' => 3,
            'deactivatedInactivity' => 4,
            'active' => 5,
        ];
    }
}
