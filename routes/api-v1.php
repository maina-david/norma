<?php

use App\Http\Controllers\Api\V1\Corpus\ReferenceController;
use App\Http\Controllers\Api\V1\Corpus\WorkController;
use App\Http\Controllers\Api\V1\Customer\LibryoController;
use App\Http\Controllers\Api\V1\Requirements\LegalReportController;
use Illuminate\Support\Facades\Route;

Route::get('/libryos', [LibryoController::class, 'index'])
    ->name('libryos.index');
Route::get('/libryos/{id}', [LibryoController::class, 'show'])
    ->name('libryos.show');

Route::get('/legislation/report/{libryo}', [LegalReportController::class, 'legalReport'])
    ->name('legislation.legal-report');

Route::get('/legislation/sections/{work}/{libryo}', [ReferenceController::class, 'forLibryoForWork'])
    ->name('legislation.sections.for.work');

Route::get('/legislation/items/{id}', [ReferenceController::class, 'show'])
    ->name('legislation.items.show');

Route::get('/registers/{id}', [WorkController::class, 'show'])
    ->name('registers.show');
