<?php

use App\Http\Controllers\Api\V2\Assess\AssessmentItemController;
use App\Http\Controllers\Api\V2\Assess\AssessmentRiskMetricsController;
use App\Http\Controllers\Api\V2\Compilation\ContextQuestionForNormaController;
use App\Http\Controllers\Api\V2\Corpus\ForOrganisationReferenceController;
use App\Http\Controllers\Api\V2\Corpus\WorkForOrganisationController;
use App\Http\Controllers\Api\V2\Customer\NormaController;
use App\Http\Controllers\Api\V2\Customer\NormaForOrganisationController;
use App\Http\Controllers\Api\V2\Customer\OrganisationForPartnerController;
use App\Http\Controllers\Api\V2\Geonames\LocationSearchController;
use App\Http\Controllers\Api\V2\Notify\FilteredLegalUpdateController;
use App\Http\Controllers\Api\V2\Notify\LegalUpdateController;
use App\Http\Controllers\Api\V2\Notify\LegalUpdateForNormasController;
use App\Http\Controllers\Api\V2\Requirements\LegalReportController;
use App\Models\Customer\Organisation;
use Illuminate\Support\Facades\Route;

// The following end points are terminated with a 503 at the load balancer, so shouldn't be used
// someone had the old PWA installed on their desktop, which was causing these to be called repeatedly
// /api/v2/legal-updates/counts
// /api/v2/legal-updates/latest
// /api/v1/system-notifications/filtered
// /api/v2/notifications/social/unread/users/me/count
// /api/v2/users/me?activeNorma=true&include=organisations&includeMetrics=true

// Ordered Alphabetically
/*
|--------------------------------------------------------------------------
| Assessment Items
|--------------------------------------------------------------------------
*/

Route::get('/assessment-items/norma/{norma}', [AssessmentItemController::class, 'indexForNorma'])
    ->middleware(['can:view,norma'])
    ->name('assessment-items.for.norma');

Route::get('/assessment-items/{assessmentItem}/norma/{norma}', [AssessmentItemController::class, 'showForNorma'])
    ->middleware(['can:view,norma'])
    ->name('assessment-items.for.norma.show');

Route::post('/assessment-metrics/for-normas', [AssessmentRiskMetricsController::class, 'metricsForNormas'])
    ->name('assessment-metrics.for.norma');
/*
|--------------------------------------------------------------------------
| Context Questions
|--------------------------------------------------------------------------
*/

Route::get('/context-questions/norma/{norma}', [ContextQuestionForNormaController::class, 'index'])
    ->name('context-questions.norma.index');

Route::post('/context-questions/{question}/norma/{norma}/{answer}', [ContextQuestionForNormaController::class, 'store'])
    ->name('context-questions.norma.answer.store');
/*
|--------------------------------------------------------------------------
| Legal Updates
|--------------------------------------------------------------------------
*/

// has to be a POST to allow for a long list of norma's
Route::post('/legal-updates/for-normas', [LegalUpdateForNormasController::class, 'index'])
    ->name('notify.legal-updates.index.for.normas');

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

Route::get('/legislation/report/{norma}', [LegalReportController::class, 'legalReport'])
    ->name('legislation.report');

Route::get('/citations/organisation/{organisation}', [ForOrganisationReferenceController::class, 'forOrganisation'])
    ->middleware('can:view,organisation')
    ->name('corpus.references.for.organisation');

/*
|--------------------------------------------------------------------------
| Normas
|--------------------------------------------------------------------------
*/

Route::get('/normas', [NormaController::class, 'index'])
    ->name('normas.index');

Route::get('/normas/{id}', [NormaController::class, 'show'])
    ->name('normas.show');

Route::post('/hq/organisations/{organisation}/normas', [NormaForOrganisationController::class, 'store'])
    ->middleware([sprintf('can:administerOrganisation,%s,organisation', Organisation::class)])
    ->name('hq.organisations.normas.store');

Route::put('/hq/organisations/{organisation}/normas/{norma}', [NormaForOrganisationController::class, 'update'])
    ->middleware([sprintf('can:administerOrganisation,%s,organisation', Organisation::class)])
    ->name('hq.organisations.normas.update')
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
