<?php

namespace App\Http\Controllers\Log\My\Settings;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class UserLifecycleActivitiesController extends Controller
{
    /**
     * @param User $user
     *
     * @return Response
     */
    public function forUser(User $user): Response
    {
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.log.my.user-lifecycle-activity.for-user',
            'target' => 'settings-activities-for-user-' . $user->id,
            'activities' => $user->lifecycleActivities,
        ]);

        return turboStreamResponse($view);
    }
}
