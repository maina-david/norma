<?php

use App\Enums\Collaborators\DocumentType;
use App\Http\Controllers\Actions\Collaborate\ActionAreaController;
use App\Http\Controllers\Actions\Collaborate\ActionAreaJsonController;
use App\Http\Controllers\Arachno\Collaborate\ChangeAlertController;
use App\Http\Controllers\Arachno\Collaborate\CrawlerController;
use App\Http\Controllers\Arachno\Collaborate\SearchPageController;
use App\Http\Controllers\Arachno\Collaborate\SourceController;
use App\Http\Controllers\Arachno\Collaborate\SourceLocationController;
use App\Http\Controllers\Arachno\Collaborate\TagController;
use App\Http\Controllers\Arachno\Collaborate\UpdateEmailController;
use App\Http\Controllers\Arachno\Collaborate\UrlFrontierLinkController;
use App\Http\Controllers\Arachno\Collaborate\UrlFrontierLinkForCrawlController;
use App\Http\Controllers\Assess\Collaborate\AssessmentItemContextQuestionController;
use App\Http\Controllers\Assess\Collaborate\AssessmentItemController;
use App\Http\Controllers\Assess\Collaborate\AssessmentItemDescriptionController;
use App\Http\Controllers\Assess\Collaborate\GuidanceNoteController;
use App\Http\Controllers\Auth\Collaborate\ProfileController;
use App\Http\Controllers\Auth\Collaborate\RoleController;
use App\Http\Controllers\Auth\My\UserSettingsController;
use App\Http\Controllers\Collaborators\Collaborate\Applications\ApplicationController;
use App\Http\Controllers\Collaborators\Collaborate\Applications\ApplicationMailController;
use App\Http\Controllers\Collaborators\Collaborate\CollaboratorController;
use App\Http\Controllers\Collaborators\Collaborate\CollaboratorImpersonationController;
use App\Http\Controllers\Collaborators\Collaborate\CollaboratorJsonController;
use App\Http\Controllers\Collaborators\Collaborate\DashboardController;
use App\Http\Controllers\Collaborators\Collaborate\ForTeamTeamRateController;
use App\Http\Controllers\Collaborators\Collaborate\GroupController;
use App\Http\Controllers\Collaborators\Collaborate\GroupJsonController;
use App\Http\Controllers\Collaborators\Collaborate\ProfileDocumentController;
use App\Http\Controllers\Collaborators\Collaborate\TeamController;
use App\Http\Controllers\Collaborators\Collaborate\TeamOutstandingPaymentRequestsController;
use App\Http\Controllers\Collaborators\NotificationController;
use App\Http\Controllers\Comments\Collaborate\CommentController;
use App\Http\Controllers\Compilation\Collaborate\CategoryContextQuestionController;
use App\Http\Controllers\Compilation\Collaborate\ContextQuestionController;
use App\Http\Controllers\Compilation\Collaborate\ContextQuestionRequirementsCollectionController;
use App\Http\Controllers\Compilation\Collaborate\ForContextQuestionContextQuestionDescriptionController;
use App\Http\Controllers\Compilation\ContextQuestionJsonController;
use App\Http\Controllers\Corpus\Collaborate\AnnotationPageController;
use App\Http\Controllers\Corpus\Collaborate\CatalogueDocController;
use App\Http\Controllers\Corpus\Collaborate\CatalogueWorkController;
use App\Http\Controllers\Corpus\Collaborate\CatalogueWorkExpressionController;
use App\Http\Controllers\Corpus\Collaborate\ContentResourceUploadController;
use App\Http\Controllers\Corpus\Collaborate\DocController;
use App\Http\Controllers\Corpus\Collaborate\DocForCatalogueDocController;
use App\Http\Controllers\Corpus\Collaborate\DocForUpdateController;
use App\Http\Controllers\Corpus\Collaborate\DocumentEditorController;
use App\Http\Controllers\Corpus\Collaborate\ForWorkReferenceController;
use App\Http\Controllers\Corpus\Collaborate\IdentifyReferenceController;
use App\Http\Controllers\Corpus\Collaborate\IdentifyReferencesPageController;
use App\Http\Controllers\Corpus\Collaborate\IdentifyTocItemStreamController;
use App\Http\Controllers\Corpus\Collaborate\IdentifyWorkExpressionContentController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceActionsController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceConsequenceController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceContentController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceDraftSummaryController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceMetadataController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceReferenceRelationController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceRequirementController;
use App\Http\Controllers\Corpus\Collaborate\ReferenceSummaryController;
use App\Http\Controllers\Corpus\Collaborate\RequirementsPreviewController;
use App\Http\Controllers\Corpus\Collaborate\ToC\ToCContentController;
use App\Http\Controllers\Corpus\Collaborate\ToC\ToCReferenceController;
use App\Http\Controllers\Corpus\Collaborate\ToCPageController;
use App\Http\Controllers\Corpus\Collaborate\WorkController;
use App\Http\Controllers\Corpus\Collaborate\WorkExpressionContentController;
use App\Http\Controllers\Corpus\Collaborate\WorkExpressionController;
use App\Http\Controllers\Corpus\Collaborate\WorkExpressionPreviewController;
use App\Http\Controllers\Corpus\Collaborate\WorkExpressionReferenceController;
use App\Http\Controllers\Corpus\Collaborate\WorkflowTaskController;
use App\Http\Controllers\Corpus\Collaborate\WorkSourceDocumentController;
use App\Http\Controllers\Corpus\Collaborate\WorkWorkRelationController;
use App\Http\Controllers\Corpus\ContentResourceController;
use App\Http\Controllers\Corpus\ContentResourceStreamController;
use App\Http\Controllers\Corpus\ForSelectorReferenceController;
use App\Http\Controllers\Corpus\ForSelectorWorkController;
use App\Http\Controllers\Corpus\TocItemStreamController;
use App\Http\Controllers\Customer\My\Settings\OrganisationController;
use App\Http\Controllers\Geonames\Collaborate\LocationController;
use App\Http\Controllers\Geonames\Settings\LocationJsonController;
use App\Http\Controllers\Lookups\Collaborate\CannedResponseController;
use App\Http\Controllers\Notify\Collaborate\LegalUpdateController;
use App\Http\Controllers\Notify\Collaborate\NotificationActionController;
use App\Http\Controllers\Notify\Collaborate\NotificationHandoverController;
use App\Http\Controllers\Notify\Collaborate\UpdateCandidateAdditionalActionsController;
use App\Http\Controllers\Notify\Collaborate\UpdateCandidateAffectedController;
use App\Http\Controllers\Notify\Collaborate\UpdateCandidateCheckController;
use App\Http\Controllers\Notify\Collaborate\UpdateCandidateController;
use App\Http\Controllers\Notify\Collaborate\UpdateCandidateHandoverUpdateController;
use App\Http\Controllers\Notify\Collaborate\UpdateCandidateJustificationController;
use App\Http\Controllers\Notify\Collaborate\UpdateCandidateNotificationStatusController;
use App\Http\Controllers\Ontology\Collaborate\CategoryController;
use App\Http\Controllers\Ontology\Collaborate\CategoryDescriptionController;
use App\Http\Controllers\Ontology\Collaborate\LegalDomainController;
use App\Http\Controllers\Ontology\Collaborate\LegalDomainLocationController;
use App\Http\Controllers\Ontology\Collaborate\LegalDomainReferenceController;
use App\Http\Controllers\Payments\Collaborate\ForTeamOutstandingPaymentRequestsController;
use App\Http\Controllers\Payments\Collaborate\ForTeamPaymentController;
use App\Http\Controllers\Payments\Collaborate\PaymentController;
use App\Http\Controllers\Payments\Collaborate\PaymentRequestController;
use App\Http\Controllers\Requirements\Collaborate\ConsequenceController;
use App\Http\Controllers\Storage\Collaborate\TaskAttachmentController;
use App\Http\Controllers\System\Collaborate\ProcessingJobController;
use App\Http\Controllers\Workflows\Collaborate\BoardController;
use App\Http\Controllers\Workflows\Collaborate\ForProjectTaskController;
use App\Http\Controllers\Workflows\Collaborate\IngestIntoProjectController;
use App\Http\Controllers\Workflows\Collaborate\MonitoringTaskController;
use App\Http\Controllers\Workflows\Collaborate\NoteController;
use App\Http\Controllers\Workflows\Collaborate\ProjectController;
use App\Http\Controllers\Workflows\Collaborate\RatingController;
use App\Http\Controllers\Workflows\Collaborate\RatingGroupController;
use App\Http\Controllers\Workflows\Collaborate\RatingTypeController;
use App\Http\Controllers\Workflows\Collaborate\TaskApplicationController;
use App\Http\Controllers\Workflows\Collaborate\TaskAssignmentController;
use App\Http\Controllers\Workflows\Collaborate\TaskController;
use App\Http\Controllers\Workflows\Collaborate\TaskCreationController;
use App\Http\Controllers\Workflows\Collaborate\TaskTypeController;
use App\Http\Controllers\Workflows\Collaborate\TaskTypeDefaultsController;
use App\Http\Controllers\Workflows\Collaborate\TaskTypeJsonController;
use App\Http\Requests\Workflows\MonitoringTaskTaskTypeDefaultsController;
use App\Models\Workflows\Note;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

/**************************************************************************
 * Action Areas
 *************************************************************************/

Route::post('/action-areas/action', [ActionAreaController::class, 'onAction'])
    ->name('action-areas.actions');

Route::get('/action-areas/json', [ActionAreaJsonController::class, 'index'])
    ->name('action-areas.index.json');

/**************************************************************************
 * Annotation
 **************************************************************************/

Route::get('/corpus/expressions/annotations/work/{work}', [AnnotationPageController::class, 'fromWork'])
    ->name('annotations.work.index');

Route::get('/legislation/register/{work}', [AnnotationPageController::class, 'fromWork'])
    ->name('annotations.register.index');

Route::get('/corpus/expressions/annotations-preview/{expression}/{task?}', [AnnotationPageController::class, 'indexForPreview'])
    ->name('annotations.preview');

Route::get('/corpus/expressions/annotations/{expression}/{task?}', [AnnotationPageController::class, 'index'])
    ->name('annotations.index')
    ->can('annotate,expression,task');

/**************************************************************************
 * Assessment Items
 **************************************************************************/
Route::post('/assessment-items/action', [AssessmentItemController::class, 'onAction'])
    ->name('assessment-items.actions');

Route::get('/assessment-items/json', [AssessmentItemController::class, 'indexJson'])
    ->name('assessment-items.index.json');

Route::group(['as' => 'assessment-items.'], function () {
    Route::resource('/assessment-items/{item}/descriptions', AssessmentItemDescriptionController::class)
        ->except(['show']);
});

/**************************************************************************
 * Assessment Item - Context Question
 **************************************************************************/

Route::post('assessment-items/{item}/context-questions', [AssessmentItemContextQuestionController::class, 'store'])
    ->name('assessment-item.context-questions.store');

Route::delete(
    'assessment-items/{item}/context-questions/{question}',
    [AssessmentItemContextQuestionController::class, 'destroy']
)->name('assessment-item.context-questions.destroy');

/**************************************************************************
 * Attachments
 **************************************************************************/

Route::get('/tasks/{task}/attachments', [TaskAttachmentController::class, 'index'])
    ->name('task.attachments.index')
    ->can('collaborate.storage.attachment.viewAny');

Route::get('/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'show'])
    ->name('task.attachments.show')
    ->can('collaborate.storage.attachment.viewAny');

Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store'])
    ->name('task.attachments.store')
    ->can('view', 'task');

Route::delete('/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy'])
    ->name('task.attachments.destroy')
    ->can('delete', 'attachment');

/**************************************************************************
 * Boards
 **************************************************************************/

Route::post('/boards/action', [BoardController::class, 'onAction'])
    ->name('boards.actions');

Route::get('/boards/{board}/task-types/{task_type}', [TaskTypeDefaultsController::class, 'index'])
    ->name('boards.task-types.index')
    ->can('collaborate.workflows.board.view');

Route::get('/boards/{board}/task-types/{task_type}/defaults', [TaskTypeDefaultsController::class, 'show'])
    ->name('boards.task-types.defaults')
    ->can('collaborate.workflows.board.set-task-defaults');

Route::put('/boards/{board}/task-types/{task_type}/defaults', [TaskTypeDefaultsController::class, 'update'])
    ->name('boards.task-types.update')
    ->can('collaborate.workflows.board.set-task-defaults');

/**************************************************************************
 * Catalogue Docs
 **************************************************************************/
Route::get('/catalogue-docs', [CatalogueDocController::class, 'index'])
    ->name('corpus.catalogue-docs.index');
Route::get('/catalogue-docs/create', [CatalogueDocController::class, 'create'])
    ->name('corpus.catalogue-docs.create');
Route::post('/catalogue-docs', [CatalogueDocController::class, 'store'])
    ->name('corpus.catalogue-docs.store');
Route::get('/catalogue-docs/{catalogueDoc}', [CatalogueDocController::class, 'show'])
    ->name('corpus.catalogue-docs.show');

Route::post('/catalogue-docs/action', [CatalogueDocController::class, 'onAction'])
    ->name('corpus.catalogue-docs.actions');
Route::get('/catalogue-docs/{catalogueDoc}/docs', [DocForCatalogueDocController::class, 'index'])
    ->name('corpus.catalogue-docs.docs.index');

Route::get('/catalogue-docs/{catalogueDoc}/docs/create', [DocForCatalogueDocController::class, 'create'])
    ->name('corpus.catalogue-docs.docs.create');
Route::post('/catalogue-docs/{catalogueDoc}/docs/{doc}/expression/create', [DocController::class, 'createExpression'])
    ->name('corpus.catalogue-docs.docs.create-expression');
Route::post('/catalogue-docs/{catalogueDoc}/docs', [DocForCatalogueDocController::class, 'store'])
    ->name('corpus.catalogue-docs.docs.store');
Route::get('/catalogue-docs/{catalogueDoc}/docs/{doc}', [DocForCatalogueDocController::class, 'show'])
    ->name('corpus.catalogue-docs.docs.show');

/**************************************************************************
 * Category
 **************************************************************************/

Route::group(['as' => 'ontology.categories.'], function () {
    Route::get('/topics/create/{parent?}', [CategoryController::class, 'create'])->name('create');
    Route::get('/topics/{parent?}', [CategoryController::class, 'index'])->name('index');
    Route::get('/topics/{category}/edit/{parent?}', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/topics/{category}/{parent?}', [CategoryController::class, 'update'])->name('update');
    Route::post('/topics/{parent?}', [CategoryController::class, 'store'])->name('store');
    Route::delete('/topics/{category}/{parent?}', [CategoryController::class, 'destroy'])->name('destroy');
});

Route::group(['as' => 'categories.'], function () {
    Route::resource('/topics/{category}/descriptions', CategoryDescriptionController::class)
        ->except(['show']);
});

/**************************************************************************
 * Catalogue Work Expressions
 **************************************************************************/

Route::get('catalogue-work-expressions/test', [CatalogueWorkExpressionController::class, 'test'])
    ->name('catalogue-work-expressions.test');
Route::get('catalogue-work-expressions/{expression}', [CatalogueWorkExpressionController::class, 'show'])
    ->name('catalogue-work-expressions.show');
Route::get('catalogue-work-expressions/{expression}/content', [CatalogueWorkExpressionController::class, 'content'])
    ->name('catalogue-work-expressions.content');

/**************************************************************************
 * Catalogue Works
 **************************************************************************/

Route::resource('catalogue-works', CatalogueWorkController::class)
    ->only(['index', 'destroy']);
Route::post('/catalogue-works/action', [CatalogueWorkController::class, 'onAction'])
    ->name('catalogue-works.actions');

/**************************************************************************
 * Collaborator Applications
 **************************************************************************/

Route::post('/collaborator-applications/{application}/status', [ApplicationController::class, 'approve'])
    ->name('collaborator-applications.approve')
    ->can('collaborate.collaborators.collaborator-application.update');
Route::post('/collaborator-applications/{application}/resend', [ApplicationMailController::class, 'resend'])
    ->name('collaborator-applications.resend')
    ->can('collaborate.collaborators.collaborator-application.update');
Route::delete('/collaborator-applications/{application}/status', [ApplicationController::class, 'decline'])
    ->name('collaborator-applications.decline')
    ->can('collaborate.collaborators.collaborator-application.update');
Route::post('/collaborator-applications/{application}/status/revert', [ApplicationController::class, 'revert'])
    ->name('collaborator-applications.revert')
    ->can('collaborate.collaborators.collaborator-application.update');

Route::resource('/collaborator-applications', ApplicationController::class)->except(['edit']);

/**************************************************************************
 * Collaborators
 **************************************************************************/

Route::post('/collaborators/action', [CollaboratorController::class, 'onAction'])
    ->name('collaborators.actions');

Route::post('/collaborators/import', [CollaboratorController::class, 'import'])
    ->name('collaborators.import');

Route::get('/collaborators/json', [CollaboratorJsonController::class, 'index'])
    ->name('collaborators.json.index')
    ->middleware('can:collaborate.collaborators.collaborator.search');

Route::put('/collaborators/{collaborator}/roles', [CollaboratorController::class, 'updateAccess'])
    ->name('collaborators.roles.update')
    ->middleware('can:collaborate.collaborators.collaborator.manage-access');

Route::resource('collaborators', CollaboratorController::class)->except(['edit']);

/**************************************************************************
 * Comments
 **************************************************************************/

Route::get('/comments/{comment}/edit', [CommentController::class, 'edit'])
    ->name('comments.edit')
    ->can('update', 'comment');

Route::put('/comments/{comment}', [CommentController::class, 'update'])
    ->name('comments.update')
    ->can('update', 'comment');

Route::get('/comments/{task}/{reference?}', [CommentController::class, 'index'])
    ->name('comments.index');

Route::post('/comments/{task}/{reference?}', [CommentController::class, 'store'])
    ->name('comments.store');

Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.destroy')
    ->can('delete', 'comment');

/**************************************************************************
 * Context Resource
 **************************************************************************/
Route::get('/content-resources/{resource}/{targetId}', [ContentResourceStreamController::class, 'show'])
    ->name('content-resources.show');

Route::get('/content/{path?}', [ContentResourceController::class, 'streamContent'])
    ->where('path', '(.*)')
    ->name('content-resources.stream.content');

Route::post('/content-resources', [ContentResourceUploadController::class, 'upload'])
    ->name('corpus.content-resources.upload')
    ->can('collaborate.corpus.content-resource.upload');

/**************************************************************************
 * Context Question
 **************************************************************************/

Route::post('/context-questions/action', [ContextQuestionController::class, 'onAction'])
    ->name('context-questions.actions');

Route::get('context-questions/json', [ContextQuestionJsonController::class, 'index'])
    ->name('context-questions.json.index');

Route::group(['as' => 'compilation.context-questions.'], function () {
    Route::resource('/context-questions/{context_question}/context-question-descriptions', ForContextQuestionContextQuestionDescriptionController::class)
        ->except(['show']);
    Route::resource('/context-questions/{context_question}/requirements-collections', ContextQuestionRequirementsCollectionController::class)
        ->only(['index', 'create']);
    Route::post('/context-questions/{context_question}/requirements-collections', [ContextQuestionRequirementsCollectionController::class, 'storePivot'])
        ->name('requirements-collections.store');
    Route::delete('/context-questions/{context_question}/requirements-collections/{requirements_collection}', [ContextQuestionRequirementsCollectionController::class, 'destroyPivot'])
        ->name('requirements-collections.destroy');
    Route::resource('/context-questions/{context_question}/categories', CategoryContextQuestionController::class)
        ->only(['index', 'create']);
    Route::post('/context-questions/{context_question}/categories', [CategoryContextQuestionController::class, 'storePivot'])
        ->name('categories.store');
    Route::delete('/context-questions/{context_question}/categories/{category}', [CategoryContextQuestionController::class, 'destroyPivot'])
        ->name('categories.destroy');
});

/**************************************************************************
 * Crawlers
 **************************************************************************/

Route::group(['as' => 'arachno.crawlers.'], function () {
    Route::get('/crawlers/create', [CrawlerController::class, 'create'])->name('create');
    Route::get('/crawlers', [CrawlerController::class, 'index'])->name('index');
    Route::get('/crawlers/{crawler}', [CrawlerController::class, 'show'])->name('show');
    Route::get('/crawlers/{crawler}/edit', [CrawlerController::class, 'edit'])->name('edit');
    Route::put('/crawlers/{crawler}', [CrawlerController::class, 'update'])->name('update');
    Route::post('/crawlers', [CrawlerController::class, 'store'])->name('store');
    Route::delete('/crawlers/{crawler}', [CrawlerController::class, 'destroy'])->name('destroy');
    Route::post('/crawlers/{crawler}/start/{type}', [CrawlerController::class, 'startNewCrawl'])
        ->name('start-crawl')
        ->can('collaborate.arachno.crawler.start-crawl');
});
/**************************************************************************
 * Crawls
 **************************************************************************/
Route::group(['as' => 'arachno.crawls.url-frontier-links.'], function () {
    Route::get('/crawls/{crawl}/url-frontier-links', [UrlFrontierLinkForCrawlController::class, 'index'])->name('index');
    Route::get('/crawls/{crawl}/url-frontier-links/{url_frontier_link}', [UrlFrontierLinkForCrawlController::class, 'showRedirect'])->name('show');
});

/**************************************************************************
 * Document Editor
 **************************************************************************/

Route::get('/corpus/expressions/{expression}/document/file-browser', [DocumentEditorController::class, 'fileBrowser'])
    ->name('document-editor.file-browser.show');
Route::post('/corpus/expressions/{expression}/document/file-browser', [DocumentEditorController::class, 'storeFile'])
    ->name('document-editor.file-browser.store');
Route::delete('/corpus/expressions/{expression}/document/file-browser', [DocumentEditorController::class, 'deleteFile'])
    ->name('document-editor.file-browser.destroy');

Route::get('/corpus/expressions/document/{expression}/{task?}', [DocumentEditorController::class, 'index'])
    ->name('document-editor.index')
    ->can('editDocument,expression,task');
Route::put('/corpus/expressions/document/{expression}/volume/{volume}/{task?}', [DocumentEditorController::class, 'update'])
    ->name('document-editor.update')
    ->can('editDocument,expression,task');

/**************************************************************************
 * Docs
 **************************************************************************/
Route::get('/docs/for-updates', [DocForUpdateController::class, 'index'])
    ->name('corpus.docs.for-update.index')
    ->can('collaborate.corpus.doc.for-updates.viewAny');
Route::post('/docs/for-updates/create-from-doc/{doc}', [DocForUpdateController::class, 'createUpdateFromDoc'])
    ->name('corpus.docs.for-update.create-update')
    ->can('collaborate.corpus.doc.for-updates.createUpdate');
Route::get('/docs/for-updates/{doc}', [DocForUpdateController::class, 'show'])
    ->name('corpus.docs.for-update.show');
Route::get('/docs', [DocController::class, 'index'])
    ->name('corpus.docs.index');
Route::get('/docs/{doc}', [DocController::class, 'show'])
    ->name('corpus.docs.show');
Route::get('/docs/{doc}/preview', [DocController::class, 'preview'])
    ->name('corpus.docs.preview');
Route::post('/docs/{doc}/generate-work', [DocController::class, 'generateWork'])
    ->name('corpus.docs.generate.work');
/**************************************************************************
 * Groups
 **************************************************************************/

Route::get('groups/json', [GroupJsonController::class, 'index'])
    ->name('groups.json.index');

/**************************************************************************
 * Guidance Notes
 **************************************************************************/
Route::post('assessment-items/{item}/guidance-notes', [GuidanceNoteController::class, 'store'])
    ->name('guidance-notes.store');
Route::resource('guidance-notes', GuidanceNoteController::class)
    ->only(['edit', 'update', 'destroy']);

/*
|--------------------------------------------------------------------------
| Impersonation
|--------------------------------------------------------------------------
*/
Route::post('/collaborators/{collaborator}/impersonate', [CollaboratorImpersonationController::class, 'store'])
    ->name('collaborator.impersonate')
    ->middleware('can:collaborate.collaborators.collaborator.impersonate');

Route::get('/collaborators/impersonate/leave', [CollaboratorImpersonationController::class, 'destroy'])
    ->name('collaborator.impersonate.leave');

/**************************************************************************
 * Jurisdictions
 **************************************************************************/
Route::get('/jurisdictions/json', [LocationJsonController::class, 'index'])
    ->name('jurisdictions.json.index');

Route::resource('jurisdictions', LocationController::class)->only(['index', 'show']);
Route::get('/jurisdictions/{jurisdiction}/children', [LocationController::class, 'index'])
    ->name('jurisdictions.children');

/**************************************************************************
 * Legal Domains
 **************************************************************************/
Route::post('/legal-domains/action', [LegalDomainController::class, 'onAction'])
    ->name('legal-domains.actions');
Route::resource('legal-domains', LegalDomainController::class)->only(['index', 'show']);
Route::group(['as' => 'corpus.references.'], function () {
    Route::get('/references/{reference}/domains', [LegalDomainReferenceController::class, 'index'])
        ->name('legal-domains.index');
});

Route::group(['prefix' => '/legal-domains/{domain}', 'as' => 'legal-domains.'], function () {
    Route::resource('/jurisdictions', LegalDomainLocationController::class)
        ->only(['index', 'create']);
    Route::post('/jurisdictions', [LegalDomainLocationController::class, 'storePivot'])
        ->name('jurisdictions.store');
    Route::delete('/jurisdictions/{requirements_collection}', [LegalDomainLocationController::class, 'destroyPivot'])
        ->name('jurisdictions.destroy');
});

/**************************************************************************
 * Legal Updates
 **************************************************************************/

Route::get('/legal-updates/create/{task?}', [LegalUpdateController::class, 'create'])
    ->name('legal-updates.create');

Route::get('/legal-updates/{legal_update}/edit/{task?}', [LegalUpdateController::class, 'edit'])
    ->name('legal-updates.edit');

Route::put('/legal-updates/{legal_update}/publish/{task?}', [LegalUpdateController::class, 'publish'])
    ->name('legal-updates.publish')
    ->can('collaborate.notify.legal-update.publish');

Route::put('/legal-updates/{legal_update}/not-applicable/{task?}', [LegalUpdateController::class, 'notApplicable'])
    ->name('legal-updates.not-applicable')
    ->can('collaborate.notify.legal-update.publish');

Route::put('/legal-updates/{legal_update}/{task?}', [LegalUpdateController::class, 'update'])
    ->name('legal-updates.update');

Route::resource('/legal-updates', LegalUpdateController::class)->except(['edit', 'create', 'update']);

/**************************************************************************
 * Locations
 **************************************************************************/
Route::get('/locations/json', [LocationJsonController::class, 'index'])
    ->name('locations.json.index');

/**************************************************************************
 * Monitoring Tasks Config
 **************************************************************************/
Route::get('/monitoring-task-configs/{config}/task-types/{task_type}', [MonitoringTaskTaskTypeDefaultsController::class, 'index'])
    ->name('monitoring-task-configs.task-types.index')
    ->can('collaborate.workflows.monitoring-task-config.update');

Route::get('/monitoring-task-configs/{config}/task-types/{task_type}/defaults', [MonitoringTaskTaskTypeDefaultsController::class, 'show'])
    ->name('monitoring-task-configs.task-types.defaults')
    ->can('collaborate.workflows.monitoring-task-config.update');

Route::put('/monitoring-task-configs/{config}/task-types/{task_type}/defaults', [MonitoringTaskTaskTypeDefaultsController::class, 'update'])
    ->name('monitoring-task-configs.task-types.update')
    ->can('collaborate.workflows.monitoring-task-config.update');

/**************************************************************************
 * Notes
 **************************************************************************/
Route::get('/notes/works/{work}/expressions/{expression?}', [NoteController::class, 'index'])
    ->name('notes.index')
    ->can('viewAny', Note::class);

Route::post('/notes/works/{work}/expressions/{expression?}', [NoteController::class, 'store'])
    ->name('notes.store')
    ->can('create', Note::class);

Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])
    ->name('notes.edit')
    ->can('update', 'note');

Route::put('/notes/{note}', [NoteController::class, 'update'])
    ->name('notes.update')
    ->can('update', 'note');

Route::delete('/notes/{note}', [NoteController::class, 'destroy'])
    ->name('notes.destroy')
    ->can('delete', 'note');

/**************************************************************************
 * Organisations
 **************************************************************************/

Route::get('/organisations/search', [OrganisationController::class, 'indexJson'])
    ->name('organisations.search')
    ->can('collaborate.workflows.project.view');

/**************************************************************************
 * Payments
 **************************************************************************/
Route::group(['as' => 'payments.'], function () {
    Route::post('/payments/export', [PaymentController::class, 'generateCSV'])
        ->name('payments.export')
        ->can('collaborate.payments.payment.export');
    Route::get('/payments/{payment}/invoice', [PaymentController::class, 'downloadInvoice'])
        ->name('payments.invoice.download')
        ->can('downloadInvoice', 'payment');
    Route::resource('payments', PaymentController::class)->only(['index', 'show']);
    Route::delete('/payment-requests/{payment_request}', [PaymentRequestController::class, 'destroy'])
        ->name('payment-requests.destroy')
        ->can('collaborate.payments.payment-request.delete');
    Route::post('/payment-requests/{task}', [PaymentRequestController::class, 'storeForTask'])
        ->name('payment-requests.store')
        ->can('collaborate.payments.payment-request.create');
});
/**************************************************************************
 * Processing Jobs
 **************************************************************************/
Route::get('/processing-jobs', [ProcessingJobController::class, 'index'])
    ->name('processing-jobs.index');
Route::get('/processing-jobs/create', [ProcessingJobController::class, 'create'])
    ->name('processing-jobs.create');
Route::post('/processing-jobs', [ProcessingJobController::class, 'store'])
    ->name('processing-jobs.store');

/**************************************************************************
 * Profile
 **************************************************************************/
Route::post('/logout-other-devices', [UserSettingsController::class, 'logoutDevices'])
    ->name('auth.logout.devices');

Route::get('/auth/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::put('/auth/profile', [ProfileController::class, 'update'])->name('profile.update');

Route::get('/auth/profile/email', [ProfileController::class, 'show'])
    ->name('profile.email.show');
Route::put('/auth/profile/email', [UserSettingsController::class, 'updateSettingsEmail'])
    ->name('profile.email.update');

Route::put('/auth/profile/password', [UserSettingsController::class, 'updateSettingsPassword'])
    ->name('profile.password.update');

Route::put('/auth/payments', [ProfileController::class, 'payments'])->name('profile.payments');

Route::get('/auth/profile/documents/{type}', [ProfileController::class, 'getDocument'])->name('profile.document.show');
Route::put('/auth/profile/documents/{type}', [ProfileController::class, 'updateDocument'])->name('profile.document.update');

/**************************************************************************
 * Profile Documents
 **************************************************************************/

Route::get('/collaborators/documents/{document}/preview', [ProfileDocumentController::class, 'show'])
    ->name('profile.document.stream')
    ->middleware('can:collaborate.collaborators.collaborator.view');

Route::get('/collaborators/documents/{document}/download', [ProfileDocumentController::class, 'show'])
    ->name('profile.document.download')
    ->middleware('can:collaborate.collaborators.collaborator.view');

Route::put('/collaborators/{profile}/document/{type}', [ProfileDocumentController::class, 'update'])
    ->where(['type' => DocumentType::fieldsRegex()])
    ->name('profile.document.upload')
    ->middleware('can:collaborate.collaborators.collaborator.update-documents');

Route::put('/collaborators/documents/{document}/validate', [ProfileDocumentController::class, 'review'])
    ->where(['type' => DocumentType::fieldsRegex()])
    ->name('profile.document.validate')
    ->middleware('can:collaborate.collaborators.collaborator.validate-documents');

/**************************************************************************
 * Projects
 **************************************************************************/
Route::get('/projects/{project}/tasks', [ForProjectTaskController::class, 'index'])
    ->name('projects.tasks.index')
    ->middleware('can:collaborate.workflows.project.view');

Route::get('/projects/{project}/ingest', [IngestIntoProjectController::class, 'createImport'])
    ->name('projects.ingest.import')
    ->can('collaborate.corpus.work.ingest-by-spreadsheet');
Route::post('/projects/{project}/ingest', [IngestIntoProjectController::class, 'importExcel'])
    ->name('projects.ingest.import.excel')
    ->can('collaborate.corpus.work.ingest-by-spreadsheet');

/**************************************************************************
 * Notifications
 **************************************************************************/
Route::group(['as' => 'collaborators.'], function () {
    Route::resource('notifications', NotificationController::class)->only(['index']);
});

/**************************************************************************
 * Reference Content
 **************************************************************************/

Route::get('/corpus/expressions/{expression}/creation/references', [ReferenceContentController::class, 'references'])
    ->name('work-expressions.creation.references.index')
    ->can('collaborate.corpus.reference.viewAny');

Route::get('/corpus/expressions/{expression}/identify/references', [IdentifyReferenceController::class, 'references'])
    ->name('work-expressions.identify.references.index')
    ->can('collaborate.corpus.reference.viewAny');

Route::get('/corpus/expressions/{expression}/creation/references/{reference}/activate', [ReferenceContentController::class, 'activate'])
    ->name('work-expressions.creation.references.activate')
    ->can('collaborate.corpus.reference.viewAny');

Route::get('/corpus/expressions/{expression}/identify/references/{reference}/activate', [IdentifyReferenceController::class, 'activate'])
    ->name('work-expressions.identify.references.activate')
    ->can('collaborate.corpus.reference.viewAny');

Route::get('/corpus/expressions/{expression}/creation/references/{reference}/deactivate', [ReferenceContentController::class, 'deactivate'])
    ->name('work-expressions.creation.references.deactivate')
    ->can('collaborate.corpus.reference.viewAny');

Route::get('/corpus/expressions/{expression}/identify/references/{reference}/deactivate', [IdentifyReferenceController::class, 'deactivate'])
    ->name('work-expressions.identify.references.deactivate')
    ->can('collaborate.corpus.reference.viewAny');

Route::get('/corpus/expressions/{expression}/creation/references/{reference}/content', [ReferenceContentController::class, 'contentStream'])
    ->name('work-expressions.creation.references.content.show')
    ->can('collaborate.corpus.reference.viewAny');

Route::get('/corpus/expressions/{expression}/identify/references/{reference}/content', [IdentifyReferenceController::class, 'contentStream'])
    ->name('work-expressions.identify.references.content.show')
    ->can('collaborate.corpus.reference.viewAny');

Route::put('/corpus/expressions/{expression}/creation/references/{reference}/content', [ReferenceContentController::class, 'update'])
    ->name('work-expressions.creation.references.content.update')
    ->can('collaborate.corpus.reference.update');
Route::put('/corpus/expressions/{expression}/identify/references/{reference}/content', [IdentifyReferenceController::class, 'update'])
    ->name('work-expressions.identify.references.content.update')
    ->can('collaborate.corpus.reference.update');

Route::post('/corpus/expressions/{expression}/creation/references/{reference}/insert', [ReferenceContentController::class, 'insertBelow'])
    ->name('work-expressions.creation.references.insert-below')
    ->can('collaborate.corpus.reference.create');

Route::post('/corpus/expressions/{expression}/identify/references/{reference}/insert', [IdentifyReferenceController::class, 'insertBelow'])
    ->name('work-expressions.identify.references.insert-below')
    ->can('collaborate.corpus.reference.create');
Route::post('/corpus/expressions/{expression}/identify/references/{reference}/move-up', [IdentifyReferenceController::class, 'moveUp'])
    ->name('work-expressions.identify.references.move-up')
    ->can('collaborate.corpus.reference.create');
Route::post('/corpus/expressions/{expression}/identify/references/{reference}/move-down', [IdentifyReferenceController::class, 'moveDown'])
    ->name('work-expressions.identify.references.move-down')
    ->can('collaborate.corpus.reference.create');
Route::post('/corpus/expressions/{expression}/identify/references/{tocItem}/insert-from-toc', [IdentifyReferenceController::class, 'insertFromTocItem'])
    ->name('work-expressions.identify.references.insert-from-toc')
    ->can('collaborate.corpus.reference.create');
Route::post('/corpus/expressions/{expression}/identify/references/{tocItem}/update-from-toc', [IdentifyReferenceController::class, 'updateFromTocItem'])
    ->name('work-expressions.identify.references.update-from-toc')
    ->can('collaborate.corpus.reference.create');

Route::post('/corpus/expressions/{expression}/creation/references/{reference}/indent', [ReferenceContentController::class, 'indent'])
    ->name('work-expressions.creation.references.indent')
    ->can('collaborate.corpus.reference.create');

Route::post('/corpus/expressions/{expression}/creation/references/{reference}/outdent', [ReferenceContentController::class, 'outdent'])
    ->name('work-expressions.creation.references.outdent')
    ->can('collaborate.corpus.reference.create');

Route::delete('/corpus/expressions/creation/references/{reference}', [ReferenceContentController::class, 'destroy'])
    ->name('work-expressions.creation.references.destroy')
    ->can('collaborate.corpus.reference.delete');

Route::delete('/corpus/expressions/identify/references/{reference}', [IdentifyReferenceController::class, 'destroy'])
    ->name('work-expressions.identify.references.destroy')
    ->can('collaborate.corpus.reference.delete');

Route::get('/corpus/expressions/creation/{expression}/{task?}', [ReferenceContentController::class, 'index'])
    ->name('expressions.creation.index')
    ->can('bespokeReferences,expression,task');

Route::get('/corpus/expressions/old-identify/{expression}/{task?}', [IdentifyReferenceController::class, 'index'])
    ->name('expressions.identify.old.index')
    ->can('bespokeReferences,expression,task');

Route::get('/corpus/expressions/identify/{expression}/{task?}', [IdentifyReferencesPageController::class, 'index'])
    ->name('expressions.identify.index')
    ->can('bespokeReferences,expression,task');

Route::post('/corpus/expressions/{expression}/identify/references/apply-drafts', [IdentifyReferenceController::class, 'applyAllDrafts'])
    ->name('work-expressions.identify.references.apply-drafts')
    ->can('collaborate.corpus.reference.apply-content-drafts');

Route::post('/corpus/expressions/{expression}/identify/references/delete-non-requirements', [IdentifyReferenceController::class, 'deleteNonRequirements'])
    ->name('work-expressions.identify.references.delete-non-requirements')
    ->can('collaborate.corpus.reference.delete-non-requirements');

Route::post('/corpus/expressions/{expression}/identify/references/generate-drafts', [IdentifyReferenceController::class, 'generateDrafts'])
    ->name('work-expressions.identify.references.generate-drafts')
    ->can('collaborate.corpus.reference.generate-content-drafts');

Route::post('/corpus/expressions/{expression}/identify/references/{reference}/request-update', [IdentifyReferenceController::class, 'requestUpdate'])
    ->name('work-expressions.identify.references.request-update')
    ->can('collaborate.corpus.reference.request-update');

Route::get('/corpus/identify/references/{reference}/preview', [IdentifyReferenceController::class, 'previewDraft'])
    ->name('work-expressions.identify.references.preview-draft');

/**************************************************************************
 * References
 **************************************************************************/
Route::group(['as' => 'corpus.works.'], function () {
    Route::get('/works/{work}/references', [ForWorkReferenceController::class, 'index'])->name('references.index');
});

Route::group(['as' => 'corpus.'], function () {
    Route::get('/work-expressions/{expression}/references/meta/template', [ReferenceMetadataController::class, 'template'])
        ->name('references.meta.template');

    Route::get('/work-expressions/{expression}/references/bulk-meta', [ReferenceMetadataController::class, 'bulkActionsForm'])
        ->name('references.meta.bulk.show');

    Route::get('/references/{reference}/summary', [ReferenceSummaryController::class, 'show'])
        ->name('summary.show');

    Route::post('/references/{reference}/summary', [ReferenceSummaryController::class, 'store'])
        ->name('summary.store')
        ->can('collaborate.requirements.summary.create');

    Route::put('/references/{reference}/summary/draft', [ReferenceDraftSummaryController::class, 'update'])
        ->name('summary.draft.update')
        ->can('collaborate.requirements.summary.draft.update');

    Route::delete('/references/{reference}/summary/draft', [ReferenceDraftSummaryController::class, 'destroy'])
        ->name('summary.draft.destroy')
        ->can('collaborate.requirements.summary.draft.delete');

    Route::put('/references/{reference}/summary', [ReferenceDraftSummaryController::class, 'apply'])
        ->name('summary.draft.apply')
        ->can('collaborate.requirements.summary.draft.apply');

    Route::delete('/references/{reference}/summary', [ReferenceSummaryController::class, 'destroy'])
        ->name('summary.destroy')
        ->can('collaborate.requirements.summary.delete');

    Route::get('/work-expressions/{expression}/references/{reference}/meta/{relation}/create', [ReferenceMetadataController::class, 'create'])
        ->name('references.meta.create');

    Route::post('/work-expressions/{expression}/references/{reference}/meta/{relation}', [ReferenceMetadataController::class, 'store'])
        ->name('references.meta.store');

    Route::delete('/work-expressions/{expression}/references/bulk/meta/{relation}', [ReferenceMetadataController::class, 'destroyForBulk'])
        ->name('references.meta.bulk.destroy');

    Route::get('/work-expressions/{expression}/references/{reference}/meta', [ReferenceMetadataController::class, 'show'])
        ->name('references.meta.show');

    Route::delete('/work-expressions/{expression}/references/{reference}/meta/{relation}/{related}', [ReferenceMetadataController::class, 'destroy'])
        ->name('references.meta.destroy');

    Route::delete('/work-expressions/{expression}/references/{reference}/meta-drafts/{relation}/{related}', [ReferenceMetadataController::class, 'destroyDraft'])
        ->name('references.meta-drafts.destroy');

    Route::delete('/work-expressions/{expression}/references/bulk/meta-drafts/{relation}', [ReferenceMetadataController::class, 'deleteDraftsForBulk'])
        ->name('references.meta-drafts.bulk.destroy');

    Route::post('/work-expressions/{expression}/references/bulk/meta-drafts/{relation}', [ReferenceMetadataController::class, 'applyDraftsForBulk'])
        ->name('references.meta-drafts.bulk.apply');

    Route::resource('references', ReferenceController::class)->only(['show']);
});

Route::get('/references/selector/content/{reference}', [ForSelectorReferenceController::class, 'cachedContent'])
    ->name('corpus.reference.for.selector.content');
Route::get('/references/selector/{work}', [ForSelectorReferenceController::class, 'index'])
    ->name('corpus.reference.for.selector.index');

/**************************************************************************
 * Requirement Previews
 **************************************************************************/

Route::get('/requirements/preview', [RequirementsPreviewController::class, 'index'])
    ->name('corpus.requirements.preview.index')
    ->can('collaborate.corpus.work.preview');

Route::get('/requirements/preview/citation/{reference}', [RequirementsPreviewController::class, 'showReference'])
    ->name('corpus.requirements.preview.reference.show')
    ->can('collaborate.corpus.work.preview');

/**************************************************************************
 * Search Pages
 **************************************************************************/

Route::get('/search', [SearchPageController::class, 'search'])
    ->name('arachno.search-pages.search');

/**************************************************************************
 * Sources
 **************************************************************************/

Route::resource('/sources/{source}/jurisdictions', SourceLocationController::class)
    ->only(['index', 'create'])
    ->names([
        'index' => 'sources.jurisdictions.index',
        'create' => 'sources.jurisdictions.create',
    ]);
Route::post('/sources/{source}/jurisdictions', [SourceLocationController::class, 'storePivot'])
    ->name('sources.jurisdictions.store');
Route::delete('/sources/{source}/jurisdictions/{jurisdiction}', [SourceLocationController::class, 'destroyPivot'])
    ->name('sources.jurisdictions.destroy');

/**************************************************************************
 * Task Types
 **************************************************************************/
Route::get('/tags/json', [TagController::class, 'indexJson'])
    ->name('tags.index.json');

/**************************************************************************
 * Task Types
 **************************************************************************/

Route::get('/task-type/{type}/tasks', [TaskController::class, 'forTaskType'])
    ->name('task-type.tasks.index')
    ->can('collaborate.workflows.task.viewAny');

/**************************************************************************
 * Tasks
 **************************************************************************/
Route::stepper('/tasks/workflow/{board}/create', TaskCreationController::class, 'tasks.wizard', [
    'can' => 'collaborate.workflows.task.create',
]);

Route::post('/tasks/workflow/{board}', [TaskCreationController::class, 'store'])
    ->name('tasks.store')
    ->can('collaborate.workflows.task.create');

Route::post('/tasks/action', [TaskController::class, 'onAction'])
    ->name('tasks.actions');

Route::post('/tasks/{task}/status/{status}', [TaskController::class, 'setStatus'])
    ->name('tasks.status');

Route::post('/tasks/{task}/check', [TaskController::class, 'markAsChecked'])
    ->name('tasks.check');

Route::post('/tasks/{task}/assign-self', [TaskAssignmentController::class, 'assignSelf'])
    ->name('tasks.application.assign-self')
    ->can('collaborate.workflows.task.assign-to-self');

Route::delete('/tasks/{task}/assign-self', [TaskAssignmentController::class, 'removeFromSelf'])
    ->name('tasks.application.remove-assignment-from-self')
    ->can('collaborate.workflows.task.remove-assignment-from-self');

Route::post('/tasks/{task}/apply', [TaskAssignmentController::class, 'store'])
    ->name('tasks.application.create')
    ->can('collaborate.workflows.task-application.create');

Route::delete('/tasks/{task}/apply', [TaskAssignmentController::class, 'destroy'])
    ->name('tasks.application.destroy')
    ->can('collaborate.workflows.task-application.create');

Route::resource('tasks', TaskController::class)->except(['create', 'store']);

/**************************************************************************
 * Task Applications
 **************************************************************************/

Route::get('/task-applications', [TaskApplicationController::class, 'index'])
    ->name('task-applications.index');
Route::put('/task-applications/{task_application}', [TaskApplicationController::class, 'update'])
    ->name('task-applications.update');
Route::delete('/task-applications/{task_application}', [TaskApplicationController::class, 'destroy'])
    ->name('task-applications.destroy');

/**************************************************************************
 * Task Rating
 **************************************************************************/
Route::post('/task/{task}/rating', [RatingController::class, 'store'])
    ->name('task.rate')
    ->can('rate,task');
Route::delete('/task/{task}/rating', [RatingController::class, 'delete'])
    ->name('task.ratings.delete')
    ->can('collaborate.workflows.task.delete-rating');

/**************************************************************************
 * Task Types
 **************************************************************************/
Route::get('/task-types/json', [TaskTypeJsonController::class, 'index'])
    ->name('task-types.json.index');

/**************************************************************************
 * Teams
 **************************************************************************/

Route::group(['as' => 'collaborators.'], function () {
    Route::get('/teams/outstanding-payments', [TeamOutstandingPaymentRequestsController::class, 'index'])
        ->name('teams.payment-requests.outstanding.index')
        ->can('collaborate.collaborators.team.payment-request.outstanding.viewAny');
    Route::get('/teams/billing/payments', [TeamController::class, 'myPayments'])
        ->name('teams.my-payments.index');
    Route::get('/teams/{team}/payments', [ForTeamPaymentController::class, 'index'])
        ->name('teams.payments.index');
    Route::get('/teams/{team}/payment-requests/outstanding', [ForTeamOutstandingPaymentRequestsController::class, 'index'])
        ->name('teams.payment-requests.outstanding')
        ->can('collaborate.collaborators.team.view');
    Route::post('/teams/{team}/payment-requests/outstanding/pay', [ForTeamOutstandingPaymentRequestsController::class, 'payOutstanding'])
        ->name('teams.payment-requests.outstanding.pay')
        ->can('collaborate.payments.payment.create');
    Route::resource('teams.team-rates', ForTeamTeamRateController::class)->except(['show']);
    Route::get('/teams/billing', [TeamController::class, 'editMyBilling'])
        ->name('teams.billing.edit');
    Route::put('/teams/billing', [TeamController::class, 'updateMyBilling'])
        ->name('teams.billing.update');
});

/*
|--------------------------------------------------------------------------
| ToC Items
|--------------------------------------------------------------------------
*/
Route::get('/toc-items/poc-creation/{expression}/{doc}/{itemId?}', [IdentifyTocItemStreamController::class, 'toc'])
    ->name('docs.identify-toc');
Route::get('/toc-items/{doc}/{itemId?}', [TocItemStreamController::class, 'toc'])
    ->name('docs.toc');

/**************************************************************************
 * ToC Page
 **************************************************************************/

Route::delete('/toc/expressions/{expression}/references/bulk', [ToCReferenceController::class, 'bulkDelete'])
    ->name('toc.references.bulk.delete')
    ->can('collaborate.corpus.reference.toc.delete-citations');

Route::get('/toc/expressions/{expression}/content/{volume}', [ToCContentController::class, 'show'])
    ->name('toc.content.volume.show');

Route::put('/toc/expressions/{expression}/volumes/{volume}/content', [ToCContentController::class, 'update'])
    ->name('toc.content.update');

Route::post('/toc/expressions/{expression}/references/generate', [ToCReferenceController::class, 'generate'])
    ->name('toc.references.generate')
    ->can('collaborate.corpus.reference.toc.generate-citations');

Route::post('/toc/expressions/{expression}/references/volumes/validate', [ToCReferenceController::class, 'invalidVolumes'])
    ->name('toc.references.validate')
    ->can('collaborate.corpus.reference.toc.generate-citations');

Route::put('/toc/expressions/{expression}/references/apply', [ToCReferenceController::class, 'applyAll'])
    ->name('toc.work.references.apply')
    ->can('collaborate.corpus.reference.toc.apply-all-pending');

Route::put('/toc/expressions/{expression}/references/{reference}', [ToCReferenceController::class, 'update'])
    ->name('toc.references.update')
    ->can('collaborate.corpus.reference.toc.generate-citations');

Route::delete('/toc/expressions/{expression}/references/{reference}', [ToCReferenceController::class, 'delete'])
    ->name('toc.references.destroy')
    ->can('collaborate.corpus.reference.toc.delete-citations');

Route::get('/toc/expressions/{expression}/references/{volume}', [ToCReferenceController::class, 'index'])
    ->name('toc.references.volume.index');

Route::get('/corpus/expressions/toc/{expression}/{task?}', [ToCPageController::class, 'show'])
    ->name('toc.index')
    ->can('editToC,expression,task');

Route::get('/toc/expressions/{expression}/references/generate/status', [ToCReferenceController::class, 'checkBatchStatus'])
    ->name('toc.references.generate.status')
    ->can('collaborate.corpus.reference.toc.generate-citations');

/**************************************************************************
 * Update Candidates
 **************************************************************************/
Route::get('/docs/for-update/justification/{doc}', [UpdateCandidateJustificationController::class, 'index'])
    ->name('docs-for-update.justification.index');
Route::get('/docs/for-update/justification/{doc}/create', [UpdateCandidateJustificationController::class, 'create'])
    ->name('docs-for-update.justification.create')
    ->can('collaborate.corpus.doc.update-candidate.set-justification');
Route::post('/docs/for-update/justification/{doc}', [UpdateCandidateJustificationController::class, 'store'])
    ->name('docs-for-update.justification.store')
    ->can('collaborate.corpus.doc.update-candidate.set-justification');

Route::get('/docs/for-update/check/{doc}', [UpdateCandidateCheckController::class, 'index'])
    ->name('docs-for-update.check.index');
Route::get('/docs/for-update/check/{doc}/create', [UpdateCandidateCheckController::class, 'create'])
    ->name('docs-for-update.check.create')
    ->can('collaborate.corpus.doc.update-candidate.set-checked-by');
Route::post('/docs/for-update/check/{doc}', [UpdateCandidateCheckController::class, 'store'])
    ->name('docs-for-update.check.store')
    ->can('collaborate.corpus.doc.update-candidate.set-checked-by');

Route::get('/docs/for-update/affected/{doc}', [UpdateCandidateAffectedController::class, 'index'])
    ->name('docs-for-update.affected.index');
Route::get('/docs/for-update/affected/{doc}/create', [UpdateCandidateAffectedController::class, 'create'])
    ->name('docs-for-update.affected.create')
    ->can('collaborate.corpus.doc.update-candidate.set-affected-legislation');
Route::post('/docs/for-update/affected/{doc}', [UpdateCandidateAffectedController::class, 'store'])
    ->name('docs-for-update.affected.store')
    ->can('collaborate.corpus.doc.update-candidate.set-affected-legislation');
Route::post('/docs/for-update/affected/{legislation}/link', [UpdateCandidateAffectedController::class, 'linkDocAndWork'])
    ->name('docs-for-update.affected.legislation.link')
    ->can('collaborate.corpus.doc.update-candidate.link-affected-legislation');

Route::get('/docs/for-update/notification-status/{doc}', [UpdateCandidateNotificationStatusController::class, 'index'])
    ->name('docs-for-update.notification-status.index');
Route::get('/docs/for-update/notification-status/{doc}/create', [UpdateCandidateNotificationStatusController::class, 'create'])
    ->name('docs-for-update.notification-status.create')
    ->can('collaborate.corpus.doc.update-candidate.set-notification-status');
Route::post('/docs/for-update/notification-status/{doc}', [UpdateCandidateNotificationStatusController::class, 'store'])
    ->name('docs-for-update.notification-status.store')
    ->can('collaborate.corpus.doc.update-candidate.set-notification-status');

Route::get('/docs/for-update/additional-actions/{doc}', [UpdateCandidateAdditionalActionsController::class, 'index'])
    ->name('docs-for-update.additional-actions.index');
Route::get('/docs/for-update/additional-actions/{doc}/create', [UpdateCandidateAdditionalActionsController::class, 'create'])
    ->name('docs-for-update.additional-actions.create')
    ->can('collaborate.corpus.doc.update-candidate.set-additional-actions');
Route::post('/docs/for-update/additional-actions/{doc}/sync', [UpdateCandidateAdditionalActionsController::class, 'sync'])
    ->name('docs-for-update.additional-actions.sync')
    ->can('collaborate.corpus.doc.update-candidate.set-additional-actions');
Route::post('/docs/for-update/additional-actions/{doc}', [UpdateCandidateAdditionalActionsController::class, 'store'])
    ->name('docs-for-update.additional-actions.store')
    ->can('collaborate.corpus.doc.update-candidate.set-additional-actions');
Route::delete('/docs/for-update/additional-actions/{doc}/{action}', [UpdateCandidateAdditionalActionsController::class, 'destroy'])
    ->name('docs-for-update.additional-actions.destroy')
    ->can('collaborate.corpus.doc.update-candidate.set-additional-actions');

Route::get('/docs/for-update/handover-updates/{doc}/create', [UpdateCandidateHandoverUpdateController::class, 'create'])
    ->name('docs-for-update.handover-updates.create')
    ->can('collaborate.corpus.doc.update-candidate.set-handover-updates');
Route::post('/docs/for-update/handover-updates/{doc}/legislation/{legislation}/sync', [UpdateCandidateHandoverUpdateController::class, 'sync'])
    ->name('docs-for-update.handover-updates.sync')
    ->can('collaborate.corpus.doc.update-candidate.set-handover-updates');
Route::post('/docs/for-update/handover-updates/{doc}/legislation/{legislation}', [UpdateCandidateHandoverUpdateController::class, 'store'])
    ->name('docs-for-update.handover-updates.store')
    ->can('collaborate.corpus.doc.update-candidate.set-handover-updates');

Route::delete('/docs/for-update/handover-updates/{doc}/legislation/{legislation}/{update}', [UpdateCandidateHandoverUpdateController::class, 'destroy'])
    ->name('docs-for-update.handover-updates.destroy')
    ->can('collaborate.corpus.doc.update-candidate.set-handover-updates');

Route::get('/docs/for-update/tasks/{task}', [UpdateCandidateController::class, 'index'])
    ->name('docs-for-update.tasks.index');

Route::get('/docs/for-update/tasks/{task}/create', [UpdateCandidateController::class, 'create'])
    ->name('docs-for-update.tasks.create')
    ->middleware('throttle:5');

Route::post('/docs/for-update/tasks/{task}', [UpdateCandidateController::class, 'store'])
    ->name('docs-for-update.tasks.store');

Route::get('/docs/for-update/tasks/{task}/{doc}', [UpdateCandidateController::class, 'show'])
    ->name('docs-for-update.tasks.show');

Route::put('/docs/for-update/tasks/{task}/{doc}', [UpdateCandidateController::class, 'update'])
    ->name('docs-for-update.tasks.update');

Route::get('/docs/for-update/tasks/{task}/{doc}/edit', [UpdateCandidateController::class, 'edit'])
    ->name('docs-for-update.tasks.edit');

Route::delete('/docs/for-update/tasks/{task}/{doc}', [UpdateCandidateController::class, 'destroy'])
    ->name('docs-for-update.tasks.destroy');

/**************************************************************************
 * Work Expressions
 **************************************************************************/

Route::put('/work-expressions/{expression}/activate', [WorkExpressionController::class, 'activate'])
    ->name('work-expressions.activate')
    ->can('collaborate.corpus.work-expression.activate');

Route::get('/work-expressions/{expression}/content', [WorkExpressionContentController::class, 'show'])
    ->name('work-expressions.volume.show');

Route::get('/work-expressions/{expression}/identify-content', [IdentifyWorkExpressionContentController::class, 'show'])
    ->name('work-expressions.identify.show');

Route::get('/work-expressions/{expression}/source/content', [WorkExpressionContentController::class, 'showSource'])
    ->name('work-expressions.source.show');

Route::post('/work-expressions/{expression}/references/actions', [ReferenceActionsController::class, 'onAction'])
    ->name('work-expressions.references.actions');
Route::post('/work-expressions/{expression}/references/actions/check', [ReferenceActionsController::class, 'checkAction'])
    ->name('work-expressions.references.actions.check');

Route::get('/work-expressions/{expression}/references/toc', [WorkExpressionReferenceController::class, 'toc'])
    ->name('work-expressions.references.toc');
Route::get('/work-expressions/{expression}/references/filters', [WorkExpressionReferenceController::class, 'setFilters'])
    ->name('work-expressions.references.filters');
Route::get('/work-expressions/{expression}/references', [WorkExpressionReferenceController::class, 'index'])
    ->name('work-expressions.references.index');
Route::get('/work-expressions/{expression}/references/meta/frame', [ReferenceMetadataController::class, 'turboFrame'])
    ->name('work-expressions.references.meta.frame');
Route::get('/work-expressions/{expression}/references/meta', [ReferenceMetadataController::class, 'index'])
    ->name('work-expressions.references.meta.index');
Route::get('/work-expressions/{expression}/references/{reference}/activate', [WorkExpressionReferenceController::class, 'activate'])
    ->name('work-expressions.references.activate');
Route::get('/work-expressions/{expression}/references/{reference}/deactivate', [WorkExpressionReferenceController::class, 'deactivate'])
    ->name('work-expressions.references.deactivate');

Route::delete('/work-expressions/{expression}/references/{reference}/consequence/draft', [ReferenceConsequenceController::class, 'destroy'])
    ->name('work-expressions.references.consequence.draft.delete');

Route::delete('/work-expressions/{expression}/references/{reference}/consequence', [ReferenceConsequenceController::class, 'forceDelete'])
    ->name('work-expressions.references.consequence.delete')
    ->can('collaborate.corpus.reference.consequence.delete');

Route::post('/work-expressions/{expression}/references/{reference}/requirement', [ReferenceRequirementController::class, 'store'])
    ->name('work-expressions.references.requirement.store');

Route::put('/work-expressions/{expression}/references/{reference}/requirement', [ReferenceRequirementController::class, 'update'])
    ->name('work-expressions.references.requirement.apply')
    ->can('collaborate.corpus.reference.requirement.apply');

Route::delete('/work-expressions/{expression}/references/{reference}/requirement/draft', [ReferenceRequirementController::class, 'destroy'])
    ->name('work-expressions.references.requirement.draft.delete');

Route::delete('/work-expressions/{expression}/references/{reference}/requirement', [ReferenceRequirementController::class, 'forceDelete'])
    ->name('work-expressions.references.requirement.delete')
    ->can('collaborate.corpus.reference.requirement.delete-applied');

Route::post('/work-expressions/{expression}/references/{reference}/relations', [ReferenceReferenceRelationController::class, 'store'])
    ->name('work-expressions.references.relations.store');

Route::delete('/work-expressions/{expression}/references/{reference}/child/{related}', [ReferenceReferenceRelationController::class, 'deleteChild'])
    ->name('work-expressions.references.child.delete')
    ->can('collaborate.corpus.reference.link.delete');

Route::delete('/work-expressions/{expression}/references/{reference}/parent/{related}', [ReferenceReferenceRelationController::class, 'deleteParent'])
    ->name('work-expressions.references.parent.delete')
    ->can('collaborate.corpus.reference.link.delete');

/**************************************************************************
 * Work Expression Preview
 **************************************************************************/

Route::get('/corpus/expressions/preview/{expression}', [WorkExpressionPreviewController::class, 'show'])
    ->name('work-expressions.preview');

/**************************************************************************
 * Workflow Commons
 **************************************************************************/

Route::get('/workflow/work-expressions/{expression}/tasks/header', [WorkflowTaskController::class, 'header'])
    ->name('workflow.tasks.header');

/**************************************************************************
 * Works
 **************************************************************************/
Route::get('/works/{work}/source/preview/{file?}', [WorkSourceDocumentController::class, 'show'])
    ->name('works.source.preview');

Route::get('/works/{work}/work-expressions/json', [WorkExpressionController::class, 'indexForJson'])
    ->name('work-expressions.json');

Route::resource('/works/{work}/work-expressions', WorkExpressionController::class);

Route::get('/works/selector', [ForSelectorWorkController::class, 'index'])
    ->name('works.for.selector.index');

Route::group(['as' => 'works.'], function () {
    Route::resource('/works/{work}/children', WorkWorkRelationController::class)->except(['edit', 'update', 'destroy']);
    Route::resource('/works/{work}/parents', WorkWorkRelationController::class)->except(['edit', 'update', 'destroy']);

    Route::post('/works/{work}/children/actions', [WorkWorkRelationController::class, 'onAction'])
        ->name('children.actions')
        ->middleware('can:collaborate.corpus.work.detach');
    Route::post('/works/{work}/parents/actions', [WorkWorkRelationController::class, 'onAction'])
        ->name('parents.actions')
        ->middleware('can:collaborate.corpus.work.detach');
});

Route::get('/works/{work}/task/{task}', [WorkController::class, 'show'])
    ->name('works.task.show');
Route::get('/works/{work}/task/{task}/edit', [WorkController::class, 'edit'])
    ->name('works.task.edit');
Route::put('/works/{work}/task/{task}', [WorkController::class, 'update'])
    ->name('works.task.update');

Route::resource('/works', WorkController::class)->except(['create', 'destroy']);
Route::post('/works/{work}/link-to-doc', [WorkController::class, 'linkToDoc'])
    ->name('corpus.works.link-to-doc')
    ->can('collaborate.corpus.work.link-to-doc');
Route::delete('/works/{work}/link-to-doc', [WorkController::class, 'unlinkFromDoc'])
    ->name('corpus.works.unlink-from-doc')
    ->can('collaborate.corpus.work.link-to-doc');

/*************************************************************************
 * Resources
 **************************************************************************/

Route::resources([
    'action-areas' => ActionAreaController::class,
    'assessment-items' => AssessmentItemController::class,
    'boards' => BoardController::class,
    'canned-responses' => CannedResponseController::class,
    'change-alerts' => ChangeAlertController::class,
    'context-questions' => ContextQuestionController::class,
    'consequences' => ConsequenceController::class,
    'groups' => GroupController::class,
    'monitoring-task-configs' => MonitoringTaskController::class,
    'notification-actions' => NotificationActionController::class,
    'notification-handovers' => NotificationHandoverController::class,
    'projects' => ProjectController::class,
    'rating-groups' => RatingGroupController::class,
    'rating-types' => RatingTypeController::class,
    'roles' => RoleController::class,
    'sources' => SourceController::class,
    'tags' => TagController::class,
    'task-types' => TaskTypeController::class,
    'teams' => TeamController::class,
    'update-emails' => UpdateEmailController::class,
    'url-frontier-links' => UrlFrontierLinkController::class,
]);
