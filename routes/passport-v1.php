<?php

use App\Http\Controllers\Api\V2\Auth\OAuthTokenController;
use App\Http\Controllers\Auth\My\SSOLoginController;

Route::get('/login/{provider}/attempt', [SSOLoginController::class, 'tokenFromProviderCode'])
    ->name('attempt.provider.login');

// just here for legacy purposes - Isometrix still uses the v1 end point
Route::post('/oauth/token', [OAuthTokenController::class, 'issueToken'])
    ->name('passport.oauth.issue.token');
