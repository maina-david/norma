<?php

// Delete this file once no longer needed by cleanchain
use App\Http\Controllers\Api\V2\System\StatusController;
use App\Http\Controllers\Corpus\My\WorkController;

// to be removed once no longer used by cleanchain
Route::get('/works/{work}/source/preview/{file?}', [WorkController::class, 'previewSource'])
    ->where('file', '(.*)')
    ->name('corpus.works.preview.source.legacy');

Route::get('/status', StatusController::class)
    ->name('status');
