<?php

use App\Http\Controllers\Collaborators\Collaborate\Applications\StageOneController;
use App\Http\Controllers\Collaborators\Collaborate\Applications\StageTwoController;
use Illuminate\Support\Facades\Route;

Route::get('/apply-to-join-norma-turks', [StageOneController::class, 'redirectToStageOne'])
    ->name('collaborator-application.stage-one.old');

/**************************************************************************
 * Apply to Join
 **************************************************************************/

Route::get('/apply-to-join/thanks', [StageOneController::class, 'thanks'])
    ->name('collaborator-application.stage-one.thanks');

Route::stepper('/apply-to-join', StageOneController::class, 'collaborator-application.stage-one');

Route::get('/complete-application/{application}', [StageTwoController::class, 'create'])
    ->name('collaborator-application.stage-two.create');
Route::put('/complete-application/{application}', [StageTwoController::class, 'update'])
    ->name('collaborator-application.stage-two.update');
Route::get('/complete-application/{application}/thanks', [StageTwoController::class, 'thanks'])
    ->name('collaborator-application.stage-two.thanks');
