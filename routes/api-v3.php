<?php

use App\Http\Controllers\Api\V3\Assess\AssessmentItemController;
use App\Http\Controllers\Api\V3\Corpus\Collaborate\TocItemReferenceController;
use App\Http\Controllers\Api\V3\Corpus\RequirementController;
use App\Http\Controllers\Api\V3\Corpus\WorkController;
use App\Http\Controllers\Api\V3\Customer\LibryoController;
use App\Http\Controllers\Api\V3\Notify\LegalUpdateController;
use App\Http\Controllers\Api\V3\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('/organisations/{organisation}')->group(function () {
    Route::resource('assessment-items', AssessmentItemController::class)->only(['index', 'show']);
    Route::resource('requirements', RequirementController::class)->only(['index', 'show']);
    Route::resource('streams', LibryoController::class)->only(['index', 'show']);
    Route::resource('tasks', TaskController::class)->only(['index', 'show']);
    Route::resource('updates', LegalUpdateController::class)->only(['index', 'show']);
    Route::resource('works', WorkController::class)->only(['index', 'show']);
});

Route::middleware(['client.internal'])
    ->withoutMiddleware(['client.org'])
    ->group(function () {
        Route::post('/toc-items/{item}/reference', [TocItemReferenceController::class, 'store'])
            ->name('toc-items.reference.store');
    });
