<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Http\Controllers\Controller;
use App\Managers\AppManager;
use App\Models\Auth\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

class PasswordResetController extends Controller
{
    /** @var string|null */
    protected ?string $route = null;

    /**
     * Generate a password reset link.
     *
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function store(User $user): RedirectResponse
    {
        abort_unless(is_null($user->auth_provider), 404);

        $label = $user->organisations()
            ->has('whitelabel')
            ->with(['whitelabel'])
            ->whereNotNull('whitelabel_id')
            ->get(['whitelabel_id'])
            ->map->whitelabel
            ->keyBy('id')
            ->first();

        $status = Password::sendResetLink(['email' => $user->email], function ($user, $token) use ($label) {
            $payload = ['token' => $token, 'email' => $user->getEmailForPasswordReset()];
            $route = route('password.reset', $payload);

            if ($label) {
                $route = sprintf(
                    'https://%s%s',
                    AppManager::appWhiteLabelURL($label->shortname),
                    route('password.reset', $payload, false)
                );
            }

            $this->route = $route;
        });

        if ($status === PasswordBroker::RESET_LINK_SENT && $this->route) {
            Session::flash('generated-reset-token', $this->route);
        }

        if ($status === PasswordBroker::RESET_THROTTLED) {
            Session::flash('generated-reset-token-throttled', true);
        }

        return redirect()->route('my.settings.users.show', ['user' => $user->hash_id]);
    }
}
