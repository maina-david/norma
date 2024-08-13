<?php

namespace App\Http\Controllers\Notify\My;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;

/**
 * @todo remove code coverage ignore when implemented
 *
 * @codeCoverageIgnore
 */
class NotificationSubscriptionController extends Controller
{
    /**
     * Unsubscribe from notifications.
     *
     * @param User   $user
     * @param string $token
     *
     * @return void
     */
    public function destroy(User $user, string $token): void
    {
    }
}
