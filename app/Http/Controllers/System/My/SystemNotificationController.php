<?php

namespace App\Http\Controllers\System\My;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\System\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SystemNotificationController extends Controller
{
    public function dismiss(SystemNotification $notification): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $dismissedNotifications = $user->settings['dismissed_notifications'] ?? [];
        if (!in_array($notification->id, $dismissedNotifications)) {
            $dismissedNotifications[] = $notification->id;
        }

        $user->updateSetting('dismissed_notifications', $dismissedNotifications);

        return back();
    }
}
