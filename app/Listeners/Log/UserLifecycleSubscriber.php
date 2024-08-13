<?php

namespace App\Listeners\Log;

use App\Events\Log\UserLifecycleChange;
use App\Models\Log\UserLifecycleActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserLifecycleSubscriber
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param UserLifecycleChange $event
     *
     * @return Builder|Model
     */
    public function handle($event): Builder|Model
    {
        $data = [
            'user_id' => $event->getUser()->id,
            'from_lifecycle' => $event->getFromLifecycle(),
            'to_lifecycle' => $event->getToLifecycle(),
        ];

        return UserLifecycleActivity::create($data);
    }
}
