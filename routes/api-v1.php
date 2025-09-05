<?php

use App\Http\Controllers\Api\V1\Corpus\ReferenceController;
use App\Http\Controllers\Api\V1\Corpus\WorkController;
use App\Http\Controllers\Api\V1\Customer\NormaController;
use App\Http\Controllers\Api\V1\Requirements\LegalReportController;
use Illuminate\Support\Facades\Route;

Route::get('/normas', [NormaController::class, 'index'])
    ->name('normas.index');
Route::get('/normas/{id}', [NormaController::class, 'show'])
    ->name('normas.show');

Route::get('/legislation/report/{norma}', [LegalReportController::class, 'legalReport'])
    ->name('legislation.legal-report');

Route::get('/legislation/sections/{work}/{norma}', [ReferenceController::class, 'forNormaForWork'])
    ->name('legislation.sections.for.work');

Route::get('/legislation/items/{id}', [ReferenceController::class, 'show'])
    ->name('legislation.items.show');

Route::get('/registers/{id}', [WorkController::class, 'show'])
    ->name('registers.show');
