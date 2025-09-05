<?php

use App\Http\Controllers\Actions\My\ActionAreaPlannerController;
use App\Http\Controllers\Actions\My\ActionAreaReferenceController;
use App\Http\Controllers\Actions\My\ActionsProjectController;
use App\Http\Controllers\Actions\My\ActionsTaskController;
use App\Http\Controllers\Actions\My\ChecklistController;
use App\Http\Controllers\Actions\My\DashboardController as ActionsDashboardController;
use App\Http\Controllers\Assess\My\AssessmentActivityController;
use App\Http\Controllers\Assess\My\AssessmentItemController;
use App\Http\Controllers\Assess\My\AssessmentItemResponseController;
use App\Http\Controllers\Assess\My\AssessmentItemResponseReferencesController;
use App\Http\Controllers\Assess\My\UserAssessmentItemController;
use App\Http\Controllers\Auth\My\UserActivityController;
use App\Http\Controllers\Auth\My\UserAvatarController;
use App\Http\Controllers\Auth\My\UserController;
use App\Http\Controllers\Auth\My\UserSettingsController;
use App\Http\Controllers\Bookmarks\AssessmentItemBookmarkController;
use App\Http\Controllers\Bookmarks\LegalUpdateBookmarkController;
use App\Http\Controllers\Bookmarks\ReferenceBookmarkController;
use App\Http\Controllers\Comments\My\CommentController;
use App\Http\Controllers\Compilation\My\ApplicabilityHistoryController;
use App\Http\Controllers\Compilation\My\ApplicabilityReferenceController;
use App\Http\Controllers\Compilation\My\ApplicabilityRequirementsController;
use App\Http\Controllers\Compilation\My\ApplicabilityStreamController;
use App\Http\Controllers\Compilation\My\ContextQuestionController;
use App\Http\Controllers\Compilation\My\ContextQuestionNormaController;
use App\Http\Controllers\Corpus\ContentResourceController;
use App\Http\Controllers\Corpus\ContentResourceStreamController;
use App\Http\Controllers\Corpus\My\ReferenceController;
use App\Http\Controllers\Corpus\My\ReferenceStreamController;
use App\Http\Controllers\Corpus\My\WorkController;
use App\Http\Controllers\Corpus\My\WorkStreamController;
use App\Http\Controllers\Corpus\TocItemStreamController;
use App\Http\Controllers\Customer\My\NormaController;
use App\Http\Controllers\Customer\My\NormaStreamController;
use App\Http\Controllers\Dashboard\My\DashboardController;
use App\Http\Controllers\Enablon\ReportController;
use App\Http\Controllers\Geonames\My\ForFileLocationController;
use App\Http\Controllers\Geonames\My\LocationTypeStreamController;
use App\Http\Controllers\Lookups\My\TranslationStreamController;
use App\Http\Controllers\Notify\My\LegalUpdateController;
use App\Http\Controllers\Notify\My\LegalUpdateUserStreamController;
use App\Http\Controllers\Notify\My\NotificationController;
use App\Http\Controllers\Notify\My\ReminderStreamController;
use App\Http\Controllers\Ontology\My\CategoryJsonController;
use App\Http\Controllers\Ontology\My\CategoryStreamController;
use App\Http\Controllers\Ontology\My\ForFileLegalDomainController;
use App\Http\Controllers\Ontology\My\LegalDomainStreamController;
use App\Http\Controllers\Ontology\My\TagController;
use App\Http\Controllers\Ontology\My\TagStreamController;
use App\Http\Controllers\Ontology\My\UserTagController;
use App\Http\Controllers\Requirements\My\ReferenceLinksStreamController;
use App\Http\Controllers\Storage\My\FileController;
use App\Http\Controllers\Storage\My\FileStreamController;
use App\Http\Controllers\Storage\My\FolderController;
use App\Http\Controllers\Storage\My\FolderStreamController;
use App\Http\Controllers\Support\DownloadController;
use App\Http\Controllers\Support\HelpController;
use App\Http\Controllers\System\JobStatusController;
use App\Http\Controllers\System\My\SystemNotificationController;
use App\Http\Controllers\Tasks\My\TaskController;
use App\Http\Controllers\Tasks\My\TaskProjectController;
use App\Http\Controllers\Tasks\My\TaskStreamController;
use App\Http\Controllers\Tasks\TaskActivityController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

// fortify only has POST request logout
Route::get('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout.get');

Route::permanentRedirect('/auth/login', '/login');

/*
|--------------------------------------------------------------------------
| Action Areas
|--------------------------------------------------------------------------
*/
Route::middleware(['module:actions'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */
    Route::prefix('/actions')->group(function () {
        Route::resource('/projects', ActionsProjectController::class)->except('show');
        Route::post('/projects/{project}/archive', [ActionsProjectController::class, 'archive'])
            ->name('projects.archive');
        Route::post('/projects/{project}/unarchive', [ActionsProjectController::class, 'unarchive'])
            ->name('projects.unarchive');
    });

    Route::get('/actions/topics', [ActionAreaPlannerController::class, 'subjects'])
        ->name('actions.action-areas.subject.index');
    Route::get('/actions/controls', [ActionAreaPlannerController::class, 'controls'])
        ->name('actions.action-areas.controls.index');
    Route::get('/actions/requirements', [ActionAreaReferenceController::class, 'index'])
        ->name('actions.action-areas.requirements.index');

    Route::get('/actions/topics/tasks/{action}', [ActionAreaPlannerController::class, 'showAction'])
        ->name('actions.action-areas.show');
    Route::get('/actions/requirements/tasks/{reference}', [ActionAreaReferenceController::class, 'show'])
        ->name('actions.action-areas.requirements.show');

    Route::get('/actions/view/{view}', [ActionsTaskController::class, 'index'])
        ->name('actions.tasks.index');
    Route::get('/actions/dashboard', [ActionsDashboardController::class, 'index'])
        ->name('actions.tasks.dashboard');
    Route::get('/actions/{task}', [ActionsTaskController::class, 'show'])
        ->name('actions.tasks.show');
    Route::delete('/actions/{task}', [ActionsTaskController::class, 'destroy'])
        ->name('actions.tasks.destroy');

    // Actions Checklists Routes
    Route::get('/actions/my/checklists', [ChecklistController::class, 'index'])
        ->name('actions.checklists.index');
});

/*
|--------------------------------------------------------------------------
| Context Questions
|--------------------------------------------------------------------------
*/

Route::get('/applicability/reference/{reference}', [ApplicabilityReferenceController::class, 'show'])
    ->name('applicability-reference.index');

Route::get('/applicability', [ContextQuestionController::class, 'index'])
    ->name('context-questions.index');

Route::get('/applicability/requirements-setup', [ApplicabilityRequirementsController::class, 'index'])
    ->name('applicability.requirements-setup.index');

Route::get('/applicability/history', [ApplicabilityHistoryController::class, 'index'])
    ->name('applicability.history.index');

Route::post('/applicability/actions', [ContextQuestionController::class, 'indexActions'])
    ->name('context-questions.actions');
Route::post('/applicability/{question}/actions', [ContextQuestionController::class, 'actionsForQuestion'])
    ->name('context-questions.actions.for.question');

Route::get('/applicability/import', [ContextQuestionController::class, 'import'])
    ->name('compilation.context-questions.import');
Route::post('/applicability/import', [ContextQuestionController::class, 'uploadExcelImport'])
    ->name('compilation.context-questions.import.upload');
Route::get('/applicability/export', [ContextQuestionController::class, 'export'])
    ->name('compilation.context-questions.export');

Route::get('/applicability/{question}', [ContextQuestionController::class, 'show'])
    ->name('context-questions.show');

Route::get('/applicability/{question}/{norma}', [ContextQuestionController::class, 'showForNorma'])
    ->name('context-questions.norma.show');

Route::put('/applicability/{question}/{norma}', [ContextQuestionNormaController::class, 'put'])
    ->name('context-questions.norma.answer');

Route::get('/applicability/{question}/{norma}/references', [ApplicabilityStreamController::class, 'requirements'])
    ->name('references.for.context-questions.index');

Route::get('/applicability/{question}/{norma}/assessment-items', [ApplicabilityStreamController::class, 'assessmentItems'])
    ->name('assessment-items.for.context-questions.index');

Route::get('/applicability/{question}/{norma}/action-areas', [ApplicabilityStreamController::class, 'actionAreas'])
    ->name('action-areas.for.context-questions.index');

Route::get('/applicability/{question}/{norma}/activities', [ApplicabilityStreamController::class, 'activities'])
    ->name('activities.for.context-questions.index');

Route::get('/applicability/{question}/{norma}/tasks', [ApplicabilityStreamController::class, 'tasks'])
    ->name('tasks.for.context-questions.index');

Route::get('/applicability/{question}/{norma}/comments', [ApplicabilityStreamController::class, 'comments'])
    ->name('comments.for.context-questions.index');

/*
|--------------------------------------------------------------------------
| Bookmarks
|--------------------------------------------------------------------------
*/
Route::post('/assess/{item}/bookmarks', [AssessmentItemBookmarkController::class, 'store'])
    ->name('assess.bookmarks.store');
Route::delete('/assess/{item}/bookmarks', [AssessmentItemBookmarkController::class, 'destroy'])
    ->name('assess.bookmarks.destroy');

Route::post('/legal-updates/{update}/bookmarks', [LegalUpdateBookmarkController::class, 'store'])
    ->name('legal-update.bookmarks.store');
Route::delete('/legal-updates/{update}/bookmarks', [LegalUpdateBookmarkController::class, 'destroy'])
    ->name('legal-update.bookmarks.destroy');

Route::post('/requirements/bookmarks/citations/{reference}', [ReferenceBookmarkController::class, 'store'])
    ->name('reference.bookmarks.store');
Route::delete('/requirements/bookmarks/citations/{reference}', [ReferenceBookmarkController::class, 'destroy'])
    ->name('reference.bookmarks.destroy');

/*
|--------------------------------------------------------------------------
| Category Routes
|--------------------------------------------------------------------------
*/

Route::get('/categories/tagging/search', [CategoryJsonController::class, 'forTagging'])
    ->name('categories.tagging.search');

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/
Route::post('/normas/{norma}/activate', [NormaController::class, 'activate'])
    ->name('normas.activate');
Route::post('/normas/activate/all', [NormaController::class, 'activateAll'])
    ->name('normas.activate.all');
Route::get('/normas/activate/all/{organisation}', [NormaController::class, 'activateAllRedirect'])
    ->name('normas.activate.all.redirect');
Route::get('/normas/activate/{norma}', [NormaController::class, 'activateRedirect'])
    ->name('normas.activate.redirect');

Route::post('/normas/switcher/search', [NormaStreamController::class, 'switcherSearch'])
    ->name('normas.switcher.search');

Route::get('/normas/switcher/recent', [NormaStreamController::class, 'switcherRecent'])
    ->name('normas.switcher.recent');

Route::get('/legal-updates/{update}/normas', [NormaStreamController::class, 'applicableForLegalUpdate'])
    ->name('notify.legal-updates.normas.index');

Route::get('/normas', [NormaController::class, 'index'])
    ->name('customer.normas.index');

Route::get('/normas/markers', [NormaController::class, 'forMarkers'])
    ->name('customer.normas.markers.index');

/*
|--------------------------------------------------------------------------
| Drives
|--------------------------------------------------------------------------
*/
Route::permanentRedirect('/files/{file}', '/drives/files/{file}');

Route::get('/drives', [FolderController::class, 'root'])
    ->name('drives.root');
Route::get('/documents/browse', function () {
    return redirect(route('my.drives.root'));
});
Route::get('/drives/files', [FileController::class, 'index'])
    ->name('drives.files.index');
Route::get('/drives/{type}', [FolderController::class, 'showRoot'])
    ->name('drives.folders.show.root');
Route::get('/drives/folders/tree/{uniqueId}/{folder?}', [FolderStreamController::class, 'tree'])
    ->name('drives.folders.tree');
Route::get('/drives/folders/{folder}', [FolderController::class, 'show'])
    ->name('drives.folders.show');

Route::get('/drives/files/for/assessment-item-response/{response}', [FileStreamController::class, 'forAssessmentItemResponse'])
    ->name('drives.files.for.assessment-item-response.index');
Route::get('/drives/files/for/task/{task}', [FileStreamController::class, 'forTask'])
    ->name('drives.files.for.task.index');
Route::get('/drives/files/for/comment/{comment}', [FileStreamController::class, 'forComment'])
    ->name('drives.files.for.comment.index');
Route::get('/drives/files/{file}', [FileController::class, 'show'])
    ->name('drives.files.show')
    ->middleware('can:view,file');
Route::get('/drives/files/stream/{file}', [FileController::class, 'stream'])
    ->name('drives.files.stream')
    ->middleware('can:view,file');
Route::get('/drives/files/download/{file}', [FileController::class, 'download'])
    ->name('drives.files.download')
    ->middleware('can:view,file');
// Route::post('/drives/files/organisation/{organisation}/upload/{folder}', [FileController::class, 'uploadToOrganisation'])
//    ->name('drives.files.upload.organisation')
//    ->middleware('can:uploadFileForOrganisation,folder,organisation');
// Route::post('/drives/files/norma/{norma}/upload/{folder}', [FileController::class, 'uploadToNorma'])
//    ->name('drives.files.upload.norma')
//    ->middleware('can:uploadFileForNorma,folder,norma');
Route::post('/drives/files/upload', [FileController::class, 'upload'])
    ->name('drives.files.upload');
Route::delete('/drives/files/bulk', [FileController::class, 'bulkDelete'])
    ->name('drives.files.bulk.destroy');
Route::delete('/drives/files/{file}', [FileController::class, 'destroy'])
    ->name('drives.files.destroy')
    ->middleware('can:delete,file');
Route::get('/drives/files/{file}/edit', [FileController::class, 'edit'])
    ->name('drives.files.edit')
    ->middleware('can:update,file');
Route::put('/drives/files/upload/{file}', [FileController::class, 'replace'])
    ->name('drives.files.replace')
    ->middleware('can:update,file');
Route::put('/drives/files/move/{file}', [FileController::class, 'move'])
    ->name('drives.files.move')
    ->middleware('can:update,file');
Route::put('/drives/files/{file}', [FileController::class, 'update'])
    ->name('drives.files.update')
    ->middleware('can:update,file');
/*
|--------------------------------------------------------------------------
| Enablon Exports
|--------------------------------------------------------------------------
*/
Route::get('/ena/exports', [ReportController::class, 'index'])
    ->name('enablon.exports.index');
Route::get('/ena/exports/{type}', [ReportController::class, 'generate'])
    ->name('enablon.exports.generate');
/*
|--------------------------------------------------------------------------
| User Tags
|--------------------------------------------------------------------------
*/
Route::get('/user-tags', [UserTagController::class, 'indexJson'])
    ->name('user-tags.index.json');

Route::get('/tags/search-suggest/{key}', [TagStreamController::class, 'searchSuggest'])
    ->name('ontology.tags.search-suggest');

Route::get('/topics/search-suggest/{key}', [CategoryStreamController::class, 'forTaggingSuggest'])
    ->name('ontology.categories.search-suggest');

Route::get('/controls/search-suggest/{key}', [CategoryStreamController::class, 'forControlsSuggest'])
    ->name('ontology.categories.controls.search-suggest');
/*
|--------------------------------------------------------------------------
| Tags
|--------------------------------------------------------------------------
*/
Route::get('/tags/json', [TagController::class, 'indexJson'])
    ->name('tags.index.json');

/*
|--------------------------------------------------------------------------
| Requirements
|--------------------------------------------------------------------------
*/
// Route::get('/requirements', [WorkController::class, 'requirements'])
//     ->name('corpus.requirements.index');
// legacy routes compatibility
Route::get('/requirements/topic/{tag}', function ($tag) {
    return redirect(route('my.corpus.requirements.index', ['tags[]' => $tag]));
});
Route::get('/legislation/topic/{tag}', function ($tag) {
    return redirect(route('my.corpus.requirements.index', ['tags[]' => $tag]));
});
Route::get('/law/discuss/searchnew/{tag}', function ($tag) {
    return redirect(route('my.corpus.requirements.index', ['tags[]' => $tag]));
});
Route::get('/law/discuss/search/{tag}', function ($tag) {
    return redirect(route('my.corpus.requirements.index', ['tags[]' => $tag]));
});

Route::get('/requirements', [WorkController::class, 'index'])
    ->name('corpus.requirements.index');
Route::get('/requirements/legal-register', [WorkController::class, 'legalRegister'])
    ->name('corpus.requirements.legal-register');
Route::get('/requirements/export/excel', [WorkController::class, 'exportAsExcel'])
    ->name('corpus.requirements.index.export.excel');
Route::get('/requirements/export/pdf', [WorkController::class, 'exportAsPDF'])
    ->name('corpus.requirements.index.export.pdf');

Route::get('/requirements/with-relations/{work}', [WorkStreamController::class, 'showWithRelations'])
    ->name('requirements.with.relation.show');

// Route::get('/assessment-item-responses/{aiResponse}/works', [WorkStreamController::class, 'indexForAssessmentItemResponse'])
//     ->name('works.for.assessment-item-response.index');
Route::get('/works/full-text/toc/{work}', [WorkStreamController::class, 'fullTextToc'])
    ->name('corpus.works.full-text.toc');
Route::get('/works/full-text/content/{work}', [WorkStreamController::class, 'fullTextContent'])
    ->name('corpus.works.full-text.content');

Route::get('/works/full-text/{work}/from-references', [WorkStreamController::class, 'fullTextFromReferences'])
    ->name('corpus.works.full-text.from-references');

Route::get('/works/full-text/{work}', [WorkStreamController::class, 'fullText'])
    ->name('works.full-text.show');

Route::any('/works/search-suggest-all/{key}', [WorkStreamController::class, 'searchSuggestAll'])
    ->name('corpus.works.search-suggest-all');

Route::get('/works/search-suggest/{key}', [WorkStreamController::class, 'searchSuggest'])
    ->name('corpus.works.search-suggest');

Route::get('/requirements/work/source-preview/{work}/{file?}', [WorkController::class, 'previewSource'])
    ->where('file', '(.*)')
    ->name('corpus.works.preview.source');

Route::get('/requirements/work/{work}', [WorkController::class, 'show'])
    ->name('corpus.works.show');

Route::get('/requirements/work/for-reference/{reference}', [WorkController::class, 'showForReference'])
    ->name('corpus.works.for-reference.show');

// legacy routes
Route::permanentRedirect('/legislation/work/{work}', '/requirements/work/{work}');
Route::permanentRedirect('/legislation/register/{work}', '/requirements/work/{work}');
Route::permanentRedirect('/legislation/register/{work}', '/requirements/work/{work}');
Route::permanentRedirect('/law/discuss/viewnew/{work}', '/requirements/work/{work}');
Route::permanentRedirect('/law/discuss/viewfulltext/{work}', '/requirements/work/{work}');

Route::get('/requirements/works/print-preview/{work}', [WorkController::class, 'printPreview'])
    ->name('corpus.works.print-preview');
/*
|--------------------------------------------------------------------------
| Requirement Links
|--------------------------------------------------------------------------
*/
Route::get('/citations/{reference}/consequences', [ReferenceLinksStreamController::class, 'consequences'])
    ->name('consequences.for.reference');
Route::get('/citations/{reference}/amendments', [ReferenceLinksStreamController::class, 'amendments'])
    ->name('amendments.for.reference');
Route::get('/citations/{reference}/read-withs', [ReferenceLinksStreamController::class, 'readWiths'])
    ->name('read-withs.for.reference');
/*
|--------------------------------------------------------------------------
| Detailed Requirements
|--------------------------------------------------------------------------
*/

Route::get('/assessment-item-responses/{aiResponse}/references', [ReferenceStreamController::class, 'indexForAssessmentItemResponse'])
    ->name('references.for.assessment-item-response.index');

Route::get('/assessment-item-responses/{response}/references/create', [AssessmentItemResponseReferencesController::class, 'create'])
    ->name('references.for.assessment-item-response.create');
Route::get('/assessment-item-responses/{response}/references/work/{work}/create', [AssessmentItemResponseReferencesController::class, 'references'])
    ->name('references.for.assessment-item-response.work.create');
Route::post('/assessment-item-responses/{response}/references', [AssessmentItemResponseReferencesController::class, 'store'])
    ->name('references.for.assessment-item-response.store');
Route::delete('/assessment-item-responses/{response}/references/{reference}', [AssessmentItemResponseReferencesController::class, 'destroy'])
    ->name('references.for.assessment-item-response.destroy');

Route::get('/citations/partial/{reference}', [ReferenceStreamController::class, 'show'])
    ->name('references.partial.show');
Route::get('/citations/for-requirements/{work}', [ReferenceStreamController::class, 'showYourRequirements'])
    ->name('references.for.requirements');
Route::any('/citations/search/suggest/{key}', [ReferenceStreamController::class, 'searchSuggest'])
    ->name('corpus.references.search-suggest');
Route::get('/citations/partial/{reference}/full-text', [ReferenceStreamController::class, 'showFullText'])
    ->name('references.partial.show.full-text');
Route::get('/requirements/citations/{reference}', [ReferenceController::class, 'show'])
    ->name('corpus.references.show');
Route::permanentRedirect('/legislation/items/{reference}', '/requirements/citations/{reference}');
Route::permanentRedirect('/legislation/citations/{reference}', '/requirements/citations/{reference}');

Route::get('/requirements/detailed', [ReferenceController::class, 'indexDetailedRequirements'])
    ->name('corpus.references.index');
/*
|--------------------------------------------------------------------------
| Notify
|--------------------------------------------------------------------------
*/
Route::get('/legal-updates', [LegalUpdateController::class, 'index'])
    ->name('notify.legal-updates.index');
Route::permanentRedirect('/legislation/notifications/{update}', '/legal-updates/{update}');
Route::permanentRedirect('/legislation/notifications', '/legal-updates');
Route::get('/legal-updates/export/excel', [LegalUpdateController::class, 'exportAsExcel'])
    ->name('legal-updates.export.excel');
Route::get('/legal-updates/export/pdf', [LegalUpdateController::class, 'exportAsPDF'])
    ->name('legal-updates.export.pdf');
Route::get('/legal-updates/preview/{update}', [LegalUpdateController::class, 'preview'])
    ->name('notify.legal-updates.preview');
Route::get('/legal-updates/{update}', [LegalUpdateController::class, 'show'])
    ->name('notify.legal-updates.show');
Route::post('/legal-updates/understood/{update}', [LegalUpdateController::class, 'markAsUnderstood'])
    ->name('notify.legal-updates.understood');
Route::get('/legal-updates/{update}/users', [LegalUpdateUserStreamController::class, 'userStatuses'])
    ->name('notify.legal-updates.users.index');

Route::get('/alerts/{readUnread}', [NotificationController::class, 'index'])
    ->name('notify.notifications.index');

/*
|--------------------------------------------------------------------------
| User Settings
|--------------------------------------------------------------------------
*/
Route::get('/my/settings/profile', [UserSettingsController::class, 'showSettingsProfile'])
    ->name('user.settings.profile.show');
Route::get('/my/settings/email', [UserSettingsController::class, 'showSettingsEmail'])
    ->name('user.settings.email.show');
Route::get('/my/settings/password', [UserSettingsController::class, 'showSettingsPassword'])
    ->name('user.settings.password.show');
Route::get('/my/settings/notifications', [UserSettingsController::class, 'showSettingsNotifications'])
    ->name('user.settings.notifications.show');
Route::get('/my/settings/security', [UserSettingsController::class, 'showSettingsSecurity'])
    ->middleware(['password.confirm'])
    ->name('user.settings.security.show');
Route::post('/logout-other-devices', [UserSettingsController::class, 'logoutDevices'])
    ->name('auth.logout.devices');
Route::put('/my/settings/profile', [UserSettingsController::class, 'updateSettingsProfile'])
    ->name('user.settings.profile.update');
Route::put('/my/settings/email', [UserSettingsController::class, 'updateSettingsEmail'])
    ->name('user.settings.email.update');
Route::put('/my/settings/password', [UserSettingsController::class, 'updateSettingsPassword'])
    ->name('user.settings.password.update');
Route::put('/my/settings/notifications', [UserSettingsController::class, 'updateSettingsNotifications'])
    ->name('user.settings.notifications.update');
Route::put('/my/settings/security', [UserSettingsController::class, 'updateSettingsSecurity'])
    ->name('user.settings.security.update');

Route::get('/avatar/{attachment}/{size?}', [UserAvatarController::class, 'downloadAvatar'])
    ->name('user.avatar.download')
    ->middleware('cache.headers:max_age=31536000,private;etag');

/*
|--------------------------------------------------------------------------
| User impersonation
|--------------------------------------------------------------------------
*/
Route::get('/users/impersonate/leave', [UserController::class, 'leaveImpersonation'])
    ->name('users.impersonate.leave');

/*
|--------------------------------------------------------------------------
| Help
|--------------------------------------------------------------------------
*/
Route::get('/help/search-portal', [HelpController::class, 'searchPortal'])
    ->name('help.search.success.portal');

/*
|--------------------------------------------------------------------------
| Comments
|--------------------------------------------------------------------------
*/
Route::get('/comments/{type}/{id}', [CommentController::class, 'forCommentable'])
    ->name('comments.for.commentable');
Route::post('/comments/{type}/{id}', [CommentController::class, 'storeForCommentable'])
    ->name('comments.for.commentable.store');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.comments.destroy')
    ->middleware(['can:delete,comment']);

/*
|--------------------------------------------------------------------------
| Tasks
|--------------------------------------------------------------------------
*/
Route::middleware(['module:tasks'])->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])
        ->name('tasks.tasks.index');
    Route::get('/tasks/calendar', [TaskController::class, 'indexForCalendar'])
        ->name('tasks.tasks.calendar.index');
    Route::get('/tasks/export/excel', [TaskController::class, 'exportAsExcel'])
        ->name('tasks.export.excel');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->name('tasks.tasks.destroy')
        ->middleware(['can:delete,task']);
    Route::get('/tasks/{task}/stream', [TaskStreamController::class, 'show'])
        ->name('tasks.tasks.stream.show')
        ->middleware(['can:view,task']);
    Route::get('/tasks/create', [TaskController::class, 'create'])
        ->name('tasks.tasks.create');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])
        ->name('tasks.tasks.edit');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->name('tasks.tasks.show');
    Route::post('/tasks', [TaskController::class, 'store'])
        ->name('tasks.tasks.store');
    Route::post('/tasks/bulk', [TaskController::class, 'storeForBulk'])
        ->name('tasks.tasks.bulk.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.tasks.update')
        ->middleware(['can:update,task']);
    Route::get('/tasks/for/{relation}/{id}/{norma}/suggest', [TaskStreamController::class, 'suggestForRelated'])
        ->name('tasks.tasks.for.related.suggest');
    Route::get('/tasks/for/{relation}/{id}/{norma}/create', [TaskStreamController::class, 'createForRelated'])
        ->name('tasks.tasks.for.related.create');
    Route::get('/tasks/for/{relation}/{id}', [TaskStreamController::class, 'indexForRelated'])
        ->name('tasks.tasks.for.related.index');
    Route::get('/tasks/follow/{task}', [TaskStreamController::class, 'follow'])
        ->name('tasks.tasks.follow');
    Route::get('/tasks/unfollow/{task}', [TaskStreamController::class, 'unfollow'])
        ->name('tasks.tasks.unfollow');
    Route::patch('/tasks/task-status/{task}', [TaskStreamController::class, 'updateStatus'])
        ->name('tasks.tasks.task-status.update')
        ->middleware(['can:update,task']);

    /*
|--------------------------------------------------------------------------
| Tasks Projects
|--------------------------------------------------------------------------
*/

    Route::get('/task-projects', [TaskProjectController::class, 'index'])
        ->name('tasks.task-projects.index');
    Route::delete('/task-projects/{project}', [TaskProjectController::class, 'destroy'])
        ->name('tasks.task-projects.destroy')
        ->middleware(['can:delete,project']);
    Route::get('/task-projects/create', [TaskProjectController::class, 'create'])
        ->name('tasks.task-projects.create');
    Route::get('/task-projects/{project}/edit', [TaskProjectController::class, 'edit'])
        ->name('tasks.task-projects.edit');
    Route::get('/task-projects/{project}', [TaskProjectController::class, 'show'])
        ->name('tasks.task-projects.show');
    Route::post('/task-projects/{project}/unarchive', [TaskProjectController::class, 'unarchive'])
        ->name('tasks.task-projects.unarchive')
        ->middleware(['can:archive,project']);
    Route::post('/task-projects/{project}/archive', [TaskProjectController::class, 'archive'])
        ->name('tasks.task-projects.archive')
        ->middleware(['can:archive,project']);
    Route::post('/task-projects', [TaskProjectController::class, 'store'])
        ->name('tasks.task-projects.store');
    Route::put('/task-projects/{project}', [TaskProjectController::class, 'update'])
        ->name('tasks.task-projects.update');
    /*
|--------------------------------------------------------------------------
| Tasks Acitivities
|--------------------------------------------------------------------------
*/
    Route::get('/tasks/{task}/activities', [TaskActivityController::class, 'indexForTask'])
        ->name('tasks.task-activities.index')
        ->middleware(['can:view,task'])
        ->middleware(['module:tasks']);
});

/*
|--------------------------------------------------------------------------
| Assess
|--------------------------------------------------------------------------
*/
Route::middleware(['module:comply'])->group(function () {
    Route::get('/assess/pending', [UserAssessmentItemController::class, 'index'])
        ->name('assess.pending');

    Route::resource('/assess', UserAssessmentItemController::class)->except(['index', 'show']);

    Route::get('/assess/dashboard', [AssessmentItemController::class, 'dashboard'])
        ->name('assess.dashboard');

    Route::get('/assess/item/{assessmentItem}', [AssessmentItemController::class, 'show'])
        ->name('assess.assessment-item.show');

    Route::get('/assess/responses/metrics', [AssessmentItemResponseController::class, 'metrics'])
        ->name('assess.assessment-item-responses.metrics');

    Route::get('/assess/metrics/export/excel', [AssessmentItemResponseController::class, 'exportAsExcel'])
        ->name('assessment-item-responses.metrics.export.excel');

    Route::get('/assess/responses/export/excel', [AssessmentItemResponseController::class, 'exportResponsesAsExcel'])
        ->name('assessment-item-responses.responses.export.excel');

    Route::get('/assess/now', [AssessmentItemResponseController::class, 'index'])
        ->name('assess.assessment-item-responses.index');

    Route::get('/assess/response/{response}/activities', [AssessmentActivityController::class, 'indexForResponse'])
        ->name('assess.assessment-activities.index.for.response');

    Route::get('/assess/response/form/{response}/{forAnswer}', [AssessmentItemResponseController::class, 'showAnswerForm'])
        ->name('assess.assessment-item-responses.answer.form');

    Route::put('/assess/response/bulk', [AssessmentItemResponseController::class, 'bulkAnswerUpdate'])
        ->name('assess.assessment-item-responses.bulk.answer.update');

    Route::put('/assess/response/{response}', [AssessmentItemResponseController::class, 'answer'])
        ->name('assess.assessment-item-responses.answer');

    Route::put('/assess/response/{response}/frequency', [AssessmentItemResponseController::class, 'updateFrequency'])
        ->name('assess.assessment-item-responses.frequency');

    Route::put('/assess/response/{response}/due', [AssessmentItemResponseController::class, 'updateNextDue'])
        ->name('assess.assessment-item-responses.next-due');

    Route::post('/assess/response/actions', [AssessmentItemResponseController::class, 'actions'])
        ->name('assess.assessment-item-responses.actions');

    Route::get('/assess/response/bulk', [AssessmentItemResponseController::class, 'editBulkAnswer'])
        ->name('assess.assessment-item-responses.bulk.answer');

    // can be removed if not reverted before monolith launch
    // Route::get('/assess/response/{aiResponse}/relations', [AssessmentItemResponseController::class, 'showRelations'])
    //     ->name('assess.assessment-item-responses.show.relations');

    Route::get('/assess/response/{aiResponse}', [AssessmentItemResponseController::class, 'show'])
        ->name('assess.assessment-item-responses.show');
});

/*
|--------------------------------------------------------------------------
| Reminders
|--------------------------------------------------------------------------
*/
Route::get('/reminders/for/{relation}/{id}/create', [ReminderStreamController::class, 'createForRelated'])
    ->name('notify.reminders.for.related.create');
Route::get('/reminders/for/{relation}/{id}', [ReminderStreamController::class, 'indexForRelated'])
    ->name('notify.reminders.for.related.index');
Route::delete('/reminders/{reminder}', [ReminderStreamController::class, 'destroy'])
    ->name('notify.reminders.destroy');
Route::post('/reminders', [ReminderStreamController::class, 'store'])
    ->name('notify.reminders.store');
/*
|--------------------------------------------------------------------------
| User Activities
|--------------------------------------------------------------------------
*/
Route::post('/user-activities/track', [UserActivityController::class, 'trackEvent'])
    ->name('user.activities.track');

/*
|--------------------------------------------------------------------------
| Translation
|--------------------------------------------------------------------------
*/
Route::post('/translate/summary/{summary}', [TranslationStreamController::class, 'translateSummary'])
    ->name('lookups.translations.translate.summary');

Route::get('/translate/reference/{reference}', [TranslationStreamController::class, 'translateReference'])
    ->name('lookups.translations.translate.reference');

Route::post('/translate/legal-update/{update}', [TranslationStreamController::class, 'translateLegalUpdate'])
    ->name('lookups.translations.translate.legal-update');

/*
|--------------------------------------------------------------------------
| Job Statuses
|--------------------------------------------------------------------------
*/
Route::post('/job-statuses/{jobId}', [JobStatusController::class, 'showByJobId'])
    ->name('job-statuses.show.by.job');

/*
|--------------------------------------------------------------------------
| Downloads
|--------------------------------------------------------------------------
*/
Route::get('/tmp/downloads/requirements/excel/{filename}', [DownloadController::class, 'downloadRequirementsExcel'])
    ->name('downloads.download.requirements.excel');
Route::get('/tmp/downloads/requirements/pdf/{filename}', [DownloadController::class, 'downloadRequirementsPDF'])
    ->name('downloads.download.requirements.pdf');
Route::get('/tmp/downloads/legal-updates/excel/{filename}', [DownloadController::class, 'downloadLegalUpdatesExcel'])
    ->name('downloads.download.legal-updates.excel');
Route::get('/tmp/downloads/legal-updates/pdf/{filename}', [DownloadController::class, 'downloadLegalUpdatesPDF'])
    ->name('downloads.download.legal-updates.pdf');
Route::get('/tmp/downloads/assess-metrics/excel/{filename}', [DownloadController::class, 'downloadAssessMetricsExcel'])
    ->name('downloads.download.assess.metrics.excel');
Route::get('/tmp/downloads/assess-responses/excel/{filename}', [DownloadController::class, 'downloadAssessResponsesExcel'])
    ->name('downloads.download.assess.responses.excel');
Route::get('/tmp/downloads/tasks/excel/{filename}', [DownloadController::class, 'downloadTasksExcel'])
    ->name('downloads.download.tasks.excel');
Route::get('/tmp/downloads/actions/excel/{filename}', [DownloadController::class, 'downloadActionsExcel'])
    ->name('downloads.download.actions.excel');
Route::get('/tmp/downloads/actions/dashboard/excel/{filename}', [DownloadController::class, 'downloadActionsStreamDataExcel'])
    ->name('downloads.download.actions.dashboard.excel');
Route::get('/tmp/downloads/enablon/{type}/excel/{filename}', [DownloadController::class, 'downloadEnablonExcel'])
    ->name('downloads.download.enablon.excel');

/*
|--------------------------------------------------------------------------
| ToC Items
|--------------------------------------------------------------------------
*/

Route::get('/toc-items/{doc}/{itemId?}', [TocItemStreamController::class, 'toc'])
    ->name('docs.toc');

/*
|--------------------------------------------------------------------------
| Content Resources
|--------------------------------------------------------------------------
*/

Route::get('/content-resources/{resource}/{targetId}', [ContentResourceStreamController::class, 'show'])
    ->name('content-resources.show');

Route::get('/content/{path?}', [ContentResourceController::class, 'streamContent'])
    ->where('path', '(.*)')
    ->name('content-resources.stream.content');

/*
|--------------------------------------------------------------------------
| Legal Domains
|--------------------------------------------------------------------------
*/

Route::get('/legal-domains/for/files/{file}', [ForFileLegalDomainController::class, 'index'])
    ->name('legal-domains.for.file.index')
    ->middleware(['can:my.storage.file.global.manage']);

Route::post('/legal-domains/for/files/{file}/actions', [ForFileLegalDomainController::class, 'actions'])
    ->name('legal-domains.for.file.actions')
    ->middleware(['can:my.storage.file.global.manage']);

Route::post('/legal-domains/for/files/{file}/add', [ForFileLegalDomainController::class, 'add'])
    ->name('legal-domains.for.file.add')
    ->middleware(['can:my.storage.file.global.manage']);

Route::get('/legal-domains/search-suggest/{key}', [LegalDomainStreamController::class, 'searchSuggest'])
    ->name('ontology.legal-domains.search-suggest');
/*
|--------------------------------------------------------------------------
| Locations
|--------------------------------------------------------------------------
*/

Route::get('/locations/for/files/{file}', [ForFileLocationController::class, 'index'])
    ->name('locations.for.file.index')
    ->middleware(['can:my.storage.file.global.manage']);

Route::post('/locations/for/files/{file}/actions', [ForFileLocationController::class, 'actions'])
    ->name('locations.for.file.actions')
    ->middleware(['can:my.storage.file.global.manage']);

Route::post('/locations/for/files/{file}/add', [ForFileLocationController::class, 'add'])
    ->name('locations.for.file.add')
    ->middleware(['can:my.storage.file.global.manage']);
/*
|--------------------------------------------------------------------------
| Locations Types
|--------------------------------------------------------------------------
*/

Route::get('/location-types/search-suggest/{key}', [LocationTypeStreamController::class, 'searchSuggest'])
    ->name('geonames.location-types.search-suggest');
/*
|--------------------------------------------------------------------------
| SystemNotifications
|--------------------------------------------------------------------------
*/

Route::post('/system-notifications/dismiss/{notification}', [SystemNotificationController::class, 'dismiss'])
    ->name('system.system-notifications.dismiss');
