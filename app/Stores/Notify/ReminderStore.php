<?php

namespace App\Stores\Notify;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\Reminder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ReminderStore
{
    public const DEFAULT_TIME = '04:00';

    /**
     * @param array<string, mixed> $input
     *
     * @return Reminder
     */
    public function createFromInput(array $input, User $user, Organisation $organisation, ?Libryo $libryo): Reminder
    {
        $date = ($input['remind_on_date'] ?? Carbon::now()->addDays(3)->format('Y-m-d')) . ' ' . ($input['remind_on_time'] ?? static::DEFAULT_TIME) . ':00';

        $data = Arr::except($input, ['remind_on_date', 'remind_on_time']);
        $remindOn = $user->toUserDate($date);

        $remindOn->setTimezone('UTC');

        if (isset($data['remind_whom'])) {
            if ($data['remind_whom'] === 'self') {
            } elseif ($data['remind_whom'] === 'libryo' && $libryo) {
                $data['place_id'] = $libryo->id;
            } else {
                $data['organisation_id'] = $organisation->id;
            }
            unset($data['remind_whom']);
        }

        /** @var Reminder */
        return Reminder::create(array_merge([
            'remind_on' => $remindOn,
            'author_id' => $user->id,
        ], $data));
    }
}
