<?php

use App\Http\Controllers\System\Admin\ApiLogController;
use App\Http\Controllers\System\Admin\SystemNotificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::view('/dashboard', 'welcome')->name('dashboard');

/**************************************************************************
 * Resources
 **************************************************************************/

Route::resources([
    'system-notifications' => SystemNotificationController::class,
]);

Route::resources([
    'api-logs' => ApiLogController::class,
]);
