<?php

use App\Http\Controllers\Auth\My\SSOLoginController;
use App\Http\Controllers\Auth\SAML\SAMLAuthUserController;
use App\Http\Controllers\Auth\SAML\SAMLMetadataController;
use App\Http\Controllers\Auth\SAML\SAMLSessionController;
use App\Http\Controllers\Notify\My\LegalUpdateGuestController;
use App\Http\Controllers\Notify\My\NotificationSubscriptionController;
use App\Models\Auth\User;
use App\Notifications\Comments\MentionedNotification;
use Illuminate\Mail\Markdown;

/**************************************************************************
 * SAML
 **************************************************************************/
Route::get('/auth/saml-login/{slug}/conflict', [SAMLAuthUserController::class, 'create'])->name('saml.conflict.create');
Route::post('/auth/saml-login/{slug}/conflict', [SAMLAuthUserController::class, 'store'])->name('saml.conflict.store');

Route::get('/auth/saml/{slug}', [SAMLSessionController::class, 'index'])->name('saml.login.index');
Route::get('/auth/saml/{slug}/login', [SAMLSessionController::class, 'show'])->name('saml.login.start');
Route::any('/auth/saml/{slug}/callback', [SAMLSessionController::class, 'store'])->name('saml.login.complete');
Route::any('/auth/saml/{slug}/logout', [SAMLSessionController::class, 'destroy'])->name('saml.logout');
Route::any('/auth/saml/{slug}/metadata', [SAMLMetadataController::class, 'index'])->name('saml.metadata.index');

/**************************************************************************
 * OAuth
 **************************************************************************/
Route::get('/oauth_callback', [SSOLoginController::class, 'fromProviderCode'])
    ->name('oauth.provider.legacy.callback');

Route::get('/oauth/callback', [SSOLoginController::class, 'fromProviderCode'])
    ->name('oauth.provider.callback');

Route::get('/oauth/redirect', [SSOLoginController::class, 'redirect'])
    ->name('oauth.provider.redirect');

Route::get('/user/{user}/notifications/preferences/{token}', [NotificationSubscriptionController::class, 'destroy'])
    ->name('notifications.unsubscribe');

Route::get('/pub/legal-updates/preview/{update}', [LegalUpdateGuestController::class, 'preview'])
    ->middleware(['throttle:guest'])
    ->name('guest.legal-updates.preview');

Route::permanentRedirect('/auth/password_reset/{token}', '/reset-password/{token}');
Route::permanentRedirect('/auth/forgot_password', '/forgot-password');

if (app()->environment('local')) {
    Route::get('/mailable', function () {
        return new App\Mail\Collaborators\TeamDetailsUpdated(1, ['title']);
    });
    Route::get('/mailable-notification', function () {
        $markdown = new Markdown(view(), config('mail.markdown'));
        $mail = (new MentionedNotification(6408))->toMail(User::find(1));

        return $markdown->render('vendor.notifications.email', $mail->toArray());
    });
}
