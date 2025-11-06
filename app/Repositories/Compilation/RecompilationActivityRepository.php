<?php

namespace App\Repositories\Compilation;

use App\Contracts\Compilation\RecompilationEventInterface;
use App\Enums\Compilation\RecompilationActivityType;
use App\Models\Auth\User;
use App\Models\Compilation\RecompilationActivity;
use App\Models\Customer\Norma;

class RecompilationActivityRepository
{
    /**
     * Log a new activity.
     *
     * @param User                      $user
     * @param RecompilationActivityType $type
     * @param Norma                    $norma
     * @param array<string, mixed>      $details
     *
     * @return RecompilationActivity
     */
    protected function addActivity(
        User $user,
        RecompilationActivityType $type,
        Norma $norma,
        array $details,
    ): RecompilationActivity {
        return RecompilationActivity::create([
            'user_id' => $user->id,
            'place_id' => $norma->id ?? null,
            'activity_type' => $type->value,
            'details' => $details,
        ]);
    }

    /**
     * @param RecompilationEventInterface $event
     *
     * @return RecompilationActivity
     **/
    public function addRecompilationActivityEvent(RecompilationEventInterface $event): RecompilationActivity
    {
        return $this->addActivity(
            $event->getUser(),
            $event->getActivityType(),
            $event->getNorma(),
            $event->toArray()
        );
    }
}
