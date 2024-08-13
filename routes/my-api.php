<?php

use App\Http\Controllers\Api\Internals\My\Actions\ActionAreaListingController;
use App\Http\Controllers\Api\Internals\My\Actions\ActionAreaReferenceController;
use App\Http\Controllers\Api\Internals\My\Actions\ChecklistController;
use App\Http\Controllers\Api\Internals\My\Actions\DashboardController;
use App\Http\Controllers\Api\Internals\My\Actions\MetricsController;
use App\Http\Controllers\Api\Internals\My\Actions\ProjectController;
use App\Http\Controllers\Api\Internals\My\Actions\TaskController;
use App\Http\Controllers\Api\Internals\My\Activities\ActivityController;
use App\Http\Controllers\Api\Internals\My\Auth\OrganisationUsersController;
use App\Http\Controllers\Api\Internals\My\Comments\CommentController;
use App\Http\Controllers\Api\Internals\My\Compilations\ApplicabilityListingController;
use App\Http\Controllers\Api\Internals\My\Corpus\ReferenceContentController;
use App\Http\Controllers\Api\Internals\My\Corpus\ReferenceExtractController;
use App\Http\Controllers\Api\Internals\My\Corpus\ReferenceLinkedActionAreasController;
use App\Http\Controllers\Api\Internals\My\Corpus\ReferenceReadWithController;
use App\Http\Controllers\Api\Internals\My\Corpus\ReferenceRelationsController;
use App\Http\Controllers\Api\Internals\My\Corpus\ReferenceSummaryController;
use App\Http\Controllers\Api\Internals\My\Customer\LibryoController;
use App\Http\Controllers\Api\Internals\My\Notify\ReminderController;
use App\Http\Controllers\Api\Internals\My\Ontology\CategoryController;
use App\Http\Controllers\Api\Internals\My\Storage\FileController;
use App\Http\Controllers\Api\Internals\My\Storage\FileRelationsController;
use App\Http\Controllers\Api\Internals\My\Storage\FolderController;
use App\Http\Controllers\Api\Internals\My\System\JobStatusController;
use Illuminate\Support\Facades\Route;

/**************************************************************************
 * Action Area Tasks
 **************************************************************************/
Route::get('/actions/metrics/statuses', [MetricsController::class, 'statuses'])
    ->name('actions.metrics.statuses');
Route::get('/actions/metrics/impact', [MetricsController::class, 'impact'])
    ->name('actions.metrics.impact');
Route::get('/actions/metrics/creation-completion', [MetricsController::class, 'creationCompletion'])
    ->name('actions.metrics.creation-completion');
Route::get('/actions/metrics/single/{metric}', [MetricsController::class, 'singleMetric'])
    ->name('actions.metrics.single.show');

Route::get('/actions/tasks', [TaskController::class, 'index'])
    ->name('actions.tasks.index');
Route::get('/actions/tasks/dashboard', [DashboardController::class, 'index'])
    ->name('actions.tasks.dashboard.index');
Route::get('/actions/tasks/dashboard/export/{type}', [DashboardController::class, 'triggerExport'])
    ->name('actions.tasks.dashboard.export');

Route::post('/actions/tasks', [TaskController::class, 'store'])
    ->name('actions.tasks.store');
Route::get('/actions/tasks/export/{type}', [TaskController::class, 'triggerExport'])
    ->name('actions.tasks.export');
Route::get('/actions/tasks/groups/{field}', [TaskController::class, 'getGroups'])
    ->name('actions.tasks.groups.index');

Route::get('/actions/projects', [ProjectController::class, 'index'])
    ->name('actions.projects.index');
Route::get('/actions/planner/areas', [ActionAreaListingController::class, 'categories'])
    ->name('actions.planner.areas.index');
Route::get('/actions/planner/references', [ActionAreaListingController::class, 'references'])
    ->name('actions.planner.references.index');
Route::get('/actions/planner/{group}/export/{type}', [ActionAreaListingController::class, 'triggerExport'])
    ->name('actions.planner.export');

Route::get('/actions/{action}/references/{reference?}', [ActionAreaReferenceController::class, 'index'])
    ->name('actions.references.index');

Route::post('/actions/tasks/actions/{action}', [TaskController::class, 'triggerAction'])
    ->name('actions.tasks.action.trigger');
Route::get('/actions/tasks/{task}', [TaskController::class, 'show'])
    ->name('actions.tasks.show');
Route::put('/actions/tasks/{task}', [TaskController::class, 'update'])
    ->name('actions.tasks.update');
Route::post('/actions/tasks/{task}/copy', [TaskController::class, 'copy'])
    ->name('actions.tasks.copy');
Route::post('/actions/tasks/{task}/recurrence', [TaskController::class, 'updateRecurrence'])
    ->name('actions.task.recurrence.store');
Route::post('/actions/tasks/{task}/recurrence/clear', [TaskController::class, 'clearRecurrence'])
    ->name('actions.task.recurrence.clear');

Route::put('/actions/{action}/checklist/update', [ChecklistController::class, 'updateRiskOfCompliance'])->name('actions.checklist.update');

Route::get('/actions/checklist/areas', [ChecklistController::class, 'checklistAreas'])->name('actions.checklist.areas.index');

/**************************************************************************
 * Activities
 **************************************************************************/

Route::get('/activities/{relation}/{related}', [ActivityController::class, 'index'])
    ->name('activities.related.index');

/**************************************************************************
 * Categories
 **************************************************************************/
Route::get('/categories/controls', [CategoryController::class, 'controls'])
    ->name('categories.controls.index');
Route::get('/categories/subjects', [CategoryController::class, 'subjects'])
    ->name('categories.subjects.index');

/**************************************************************************
 * Comments
 **************************************************************************/

Route::get('/comments/related/{relation}/{related}', [CommentController::class, 'index'])
    ->name('comments.related.index');

Route::post('/comments/related/{relation}/{related}', [CommentController::class, 'store'])
    ->name('comments.related.store');

Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.destroy');

/**************************************************************************
 * Files & Folders
 **************************************************************************/
Route::get('/storage/files/related/{relation}/{related}', [FileRelationsController::class, 'index'])
    ->name('storage.files.related.index');

Route::post('/storage/files', [FileController::class, 'store'])
    ->name('storage.files.store');

Route::post('/storage/files', [FileController::class, 'store'])
    ->name('storage.files.store');

Route::get('/storage/folders/tree/{folder?}', [FolderController::class, 'index'])
    ->name('storage.folders.index');

/**************************************************************************
 * Job Statuses
 **************************************************************************/
Route::post('/job-statuses/{job}', [JobStatusController::class, 'show'])
    ->name('job-statuses.show.by.job');

/**************************************************************************
 * Libryos
 **************************************************************************/

Route::get('/organisation/libryos', [LibryoController::class, 'index'])
    ->name('organisation.libryos.index');

/**************************************************************************
 * References
 **************************************************************************/

Route::get('/references/{reference}/content/{language?}', [ReferenceContentController::class, 'show'])
    ->name('references.content.show');
Route::get('/references/{reference}/extracts', [ReferenceExtractController::class, 'index'])
    ->name('references.extracts.index');
Route::get('/references/{reference}/summary/{language?}', [ReferenceSummaryController::class, 'show'])
    ->name('references.summary.show');
Route::get('/references/{reference}/read-withs', [ReferenceReadWithController::class, 'index'])
    ->name('references.read-withs.index');
Route::get('/references/{reference}/read-withs/{relation}', [ReferenceRelationsController::class, 'index'])
    ->name('references.read-withs.relation.index');
Route::get('/references/{reference}/action-areas', [ReferenceLinkedActionAreasController::class, 'index'])
    ->name('references.action-areas.index');
Route::get('/references/{reference}/applicability', [ApplicabilityListingController::class, 'index'])
    ->name('references.applicability.index');

/**************************************************************************
 * Reminders
 **************************************************************************/
Route::get('/reminders/{relation}/{id}', [ReminderController::class, 'index'])
    ->name('reminders.related.index');
Route::post('/reminders/{relation}/{id}', [ReminderController::class, 'store'])
    ->name('reminders.related.store');
Route::delete('/reminders/{reminder}', [ReminderController::class, 'destroy'])
    ->name('reminders.destroy');
/**************************************************************************
 * Users
 **************************************************************************/

Route::get('/organisation/users', [OrganisationUsersController::class, 'index'])
    ->name('organisation.users.index');
