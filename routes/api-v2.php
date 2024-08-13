<?php

use App\Http\Controllers\Api\V2\Assess\AssessmentItemController;
use App\Http\Controllers\Api\V2\Assess\AssessmentRiskMetricsController;
use App\Http\Controllers\Api\V2\Compilation\ContextQuestionForLibryoController;
use App\Http\Controllers\Api\V2\Corpus\ForOrganisationReferenceController;
use App\Http\Controllers\Api\V2\Corpus\WorkForOrganisationController;
use App\Http\Controllers\Api\V2\Customer\LibryoController;
use App\Http\Controllers\Api\V2\Customer\LibryoForOrganisationController;
use App\Http\Controllers\Api\V2\Customer\OrganisationForPartnerController;
use App\Http\Controllers\Api\V2\Geonames\LocationSearchController;
use App\Http\Controllers\Api\V2\Notify\FilteredLegalUpdateController;
use App\Http\Controllers\Api\V2\Notify\LegalUpdateController;
use App\Http\Controllers\Api\V2\Notify\LegalUpdateForLibryosController;
use App\Http\Controllers\Api\V2\Requirements\LegalReportController;
use App\Models\Customer\Organisation;
use Illuminate\Support\Facades\Route;

// The following end points are terminated with a 503 at the load balancer, so shouldn't be used
// someone had the old PWA installed on their desktop, which was causing these to be called repeatedly
// /api/v2/legal-updates/counts
// /api/v2/legal-updates/latest
// /api/v1/system-notifications/filtered
// /api/v2/notifications/social/unread/users/me/count
// /api/v2/users/me?activeLibryo=true&include=organisations&includeMetrics=true

// Ordered Alphabetically
/*
|--------------------------------------------------------------------------
| Assessment Items
|--------------------------------------------------------------------------
*/

Route::get('/assessment-items/libryo/{libryo}', [AssessmentItemController::class, 'indexForLibryo'])
    ->middleware(['can:view,libryo'])
    ->name('assessment-items.for.libryo');

Route::get('/assessment-items/{assessmentItem}/libryo/{libryo}', [AssessmentItemController::class, 'showForLibryo'])
    ->middleware(['can:view,libryo'])
    ->name('assessment-items.for.libryo.show');

Route::post('/assessment-metrics/for-libryos', [AssessmentRiskMetricsController::class, 'metricsForLibryos'])
    ->name('assessment-metrics.for.libryo');
/*
|--------------------------------------------------------------------------
| Context Questions
|--------------------------------------------------------------------------
*/

Route::get('/context-questions/libryo/{libryo}', [ContextQuestionForLibryoController::class, 'index'])
    ->name('context-questions.libryo.index');

Route::post('/context-questions/{question}/libryo/{libryo}/{answer}', [ContextQuestionForLibryoController::class, 'store'])
    ->name('context-questions.libryo.answer.store');
/*
|--------------------------------------------------------------------------
| Legal Updates
|--------------------------------------------------------------------------
*/

// has to be a POST to allow for a long list of libryo's
Route::post('/legal-updates/for-libryos', [LegalUpdateForLibryosController::class, 'index'])
    ->name('notify.legal-updates.index.for.libryos');

Route::get('/legal-updates/filtered', [FilteredLegalUpdateController::class, 'indexFiltered'])
    ->name('notify.legal-updates.filtered.index');

Route::get('/legal-updates', [LegalUpdateController::class, 'index'])
    ->name('notify.legal-updates.index');

Route::patch('/legal-updates/understood/{update}', [LegalUpdateController::class, 'markAsUnderstood'])
    ->name('notify.legal-updates.understood');
/*
|--------------------------------------------------------------------------
| Legislation
|--------------------------------------------------------------------------
*/

Route::get('/legislation/report/{libryo}', [LegalReportController::class, 'legalReport'])
    ->name('legislation.report');

Route::get('/citations/organisation/{organisation}', [ForOrganisationReferenceController::class, 'forOrganisation'])
    ->middleware('can:view,organisation')
    ->name('corpus.references.for.organisation');

/*
|--------------------------------------------------------------------------
| Libryos
|--------------------------------------------------------------------------
*/

Route::get('/libryos', [LibryoController::class, 'index'])
    ->name('libryos.index');

Route::get('/libryos/{id}', [LibryoController::class, 'show'])
    ->name('libryos.show');

Route::post('/hq/organisations/{organisation}/libryos', [LibryoForOrganisationController::class, 'store'])
    ->middleware([sprintf('can:administerOrganisation,%s,organisation', Organisation::class)])
    ->name('hq.organisations.libryos.store');

Route::put('/hq/organisations/{organisation}/libryos/{libryo}', [LibryoForOrganisationController::class, 'update'])
    ->middleware([sprintf('can:administerOrganisation,%s,organisation', Organisation::class)])
    ->name('hq.organisations.libryos.update')
    ->scopeBindings();
/*
|--------------------------------------------------------------------------
| Locations
|--------------------------------------------------------------------------
*/

Route::get('/locations/find-by-name', [LocationSearchController::class, 'index'])
    ->withoutMiddleware(['throttle:api'])
    ->middleware('throttle:heavy-api')
    ->name('locations.find-by-name');

/*
|--------------------------------------------------------------------------
| Organisations
|--------------------------------------------------------------------------
*/

Route::post('/hq/partners/organisations/', [OrganisationForPartnerController::class, 'store'])
    ->name('hq.partners.organisations.store');

Route::put('/hq/organisations/{organisation}', [OrganisationForPartnerController::class, 'update'])
    ->middleware([sprintf('can:administerOrganisation,%s,organisation', Organisation::class)])
    ->name('hq.partners.organisations.update');
/*
|--------------------------------------------------------------------------
| Works
|--------------------------------------------------------------------------
*/

Route::get('/works/organisation/{organisation}', [WorkForOrganisationController::class, 'forOrganisation'])
    ->middleware(['can:view,organisation'])
    ->name('works.for.organisation');
