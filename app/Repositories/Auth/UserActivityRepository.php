<?php

namespace App\Repositories\Auth;

use App\Enums\Auth\UserActivityType;
use App\Events\Auth\UserActivity\UserAccessedPlatform;
use App\Events\Auth\UserActivity\UserActivityEvent;
use App\Models\Auth\User;
use App\Models\Auth\UserActivity;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class UserActivityRepository
{
    /**
     * @param User              $user
     * @param UserActivityType  $type
     * @param string|false      $details
     * @param Norma|null       $place
     * @param Organisation|null $organisation
     *
     * @return UserActivity
     */
    public function addActivity(
        User $user,
        UserActivityType $type,
        string|false $details,
        ?Norma $place = null,
        ?Organisation $organisation = null
    ): UserActivity {
        return UserActivity::create([
            'user_id' => $user->id,
            'place_id' => $place->id ?? null,
            'organisation_id' => $organisation->id ?? null,
            'activity_type' => $type->value,
            // @codeCoverageIgnoreStart
            'details' => $details ?: null,
            // @codeCoverageIgnoreEnd
        ]);
    }

    /**
     * @param UserActivityEvent $event
     *
     * @return UserActivity
     **/
    public function addUserActivityEvent(UserActivityEvent $event): UserActivity
    {
        $activity = $this->addActivity(
            $event->getUser(),
            $event->getActivityType(),
            $event->toJson(),
            $event->getNorma(),
            $event->getOrganisation()
        );

        $this->firePlatformAccessedEvent($event->getUser());

        return $activity;
    }

    /**
     * obfuscates all personal details from user activity.
     *
     * @param User $user
     *
     * @return void
     */
    public function forgetActivityForUser(User $user): void
    {
        UserActivity::where('user_id', $user->id)
            ->chunk(100, function ($activities) use ($user) {
                foreach ($activities as $activity) {
                    $details = (array) json_decode($activity->details ?? '[]');

                    if (array_key_exists('user', $details)) {
                        $details['user'] = [
                            'id' => $user->id,
                            'fname' => 'Forgotten',
                            'sname' => 'User',
                        ];
                    }

                    $details['status'] = ['forgotten'][0];

                    /** @var string $details */
                    $details = json_encode($details);
                    $activity->details = $details;
                    $activity->save();
                }
            });
    }

    /**
     * Fire the platform accessed event.
     *
     * @param User $user
     *
     * @return void
     */
    public function firePlatformAccessedEvent(User $user): void
    {
        $cacheKey = config('cache-keys.auth.last_login_prefix') . $user->id;

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, Carbon::now()->toDateTimeString(), Carbon::now()->endOfDay());

        event(new UserAccessedPlatform($user));
    }
}
