<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Managers\WhiteLabelManager;
use App\Models\Auth\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LoginRequest::class, \App\Http\Requests\Auth\LoginRequest::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setViews();

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        // override fortify login because only active users should be able to log in
        Fortify::authenticateUsing(function (Request $request) {
            /** @var User|null $user */
            $user = User::where('email', $request->email)
                ->active()
                ->first();

            if (
                $user
                // @phpstan-ignore-next-line
                && Hash::check($request->password, $user->password)
            ) {
                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->get('email') . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            /** @todo remove code coverage ignore once implemented */
            // @codeCoverageIgnoreStart
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * Set the views to be used by the app.
     *
     * @return void
     */
    protected function setViews(): void
    {
        Fortify::loginView(function () {
            $whitelabel = app(WhiteLabelManager::class)->current();

            return view('auth.login', [
                'ssoEnabled' => $whitelabel->authProvider() !== 'libryo',
                'whitelabel' => $whitelabel,
            ]);
        });
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
    }
}
