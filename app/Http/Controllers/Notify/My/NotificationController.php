<?php

namespace App\Http\Controllers\Notify\My;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * @return Response
     */
    public function index(string $readUnread): Response
    {
        /** @var User */
        $user = Auth::user();

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.notify.notification.render-feed',
            'target' => 'notify-notifications-for-' . $readUnread . '-' . $user->id,
            'unread' => $readUnread === 'unread' ? true : false,
            'user' => $user,
        ]);

        return turboStreamResponse($view);
    }
}
