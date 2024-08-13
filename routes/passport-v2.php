<?php

use App\Http\Controllers\Api\V2\Auth\OAuthTokenController;

Route::post('/oauth/token', [OAuthTokenController::class, 'issueToken'])
    ->name('passport.oauth.issue.token');
