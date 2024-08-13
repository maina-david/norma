<?php

use App\Http\Controllers\Actions\Collaborate\ActionAreaJsonController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\CatalogueDocController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceChildrenController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceCommentsController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceContentController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceContentExtractController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceMetadataController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceParentsController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceRequirementController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceSummaryController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\ReferenceSummaryDraftController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\TocItemController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\WorkController;
use App\Http\Controllers\Api\Internals\Collaborate\Corpus\WorkExpressionContentController;
use App\Http\Controllers\Api\Internals\Collaborate\Lookups\ActionAreaSuggestionController;
use App\Http\Controllers\Api\Internals\Collaborate\Lookups\AssessmentItemSuggestionController;
use App\Http\Controllers\Api\Internals\Collaborate\Lookups\CategorySuggestionController;
use App\Http\Controllers\Api\Internals\Collaborate\Lookups\ContextQuestionSuggestionController;
use App\Http\Controllers\Api\Internals\Collaborate\Lookups\LegalDomainSuggestionController;
use App\Http\Controllers\Arachno\Collaborate\SourceController;
use App\Http\Controllers\Arachno\Collaborate\TagController;
use App\Http\Controllers\Assess\Collaborate\AssessmentItemController;
use App\Http\Controllers\Compilation\ContextQuestionJsonController;
use App\Http\Controllers\Geonames\Settings\LocationJsonController;
use App\Http\Controllers\Ontology\Collaborate\CategoryJsonController;
use App\Http\Controllers\Ontology\Collaborate\LegalDomainController;
use Illuminate\Support\Facades\Route;

/**************************************************************************
 * Catalogue Docs
 **************************************************************************/
Route::get('/catalogue-docs', [CatalogueDocController::class, 'index'])
    ->name('catalogue-docs.index');

/**************************************************************************
 * Json Searches
 **************************************************************************/
Route::get('/action-areas/json', [ActionAreaJsonController::class, 'index'])
    ->name('action-areas.index.json');
Route::get('/assessment-items/json', [AssessmentItemController::class, 'indexJson'])
    ->name('assessment-items.index.json');
Route::get('/categories/json', [CategoryJsonController::class, 'index'])
    ->name('categories.index.json');
Route::get('/context-questions/json', [ContextQuestionJsonController::class, 'index'])
    ->name('context-questions.index.json');
Route::get('/legal-domains/json', [LegalDomainController::class, 'indexJson'])
    ->name('legal-domains.index.json');
Route::get('/locations/json', [LocationJsonController::class, 'index'])
    ->name('locations.index.json');
Route::get('/sources/json', [SourceController::class, 'json'])
    ->name('sources.index.json');
Route::get('/tags/json', [TagController::class, 'indexJson'])
    ->name('tags.index.json');

/**************************************************************************
 * Reference Children & Parents
 **************************************************************************/

Route::post('/references/{reference}/type/{type}/children', [ReferenceChildrenController::class, 'store'])
    ->name('references.children.store');
Route::post('/references/{reference}/type/{type}/parents', [ReferenceParentsController::class, 'store'])
    ->name('references.parents.store');
Route::delete('/references/{reference}/type/{type}/children/{related?}', [ReferenceChildrenController::class, 'delete'])
    ->name('references.children.destroy');
Route::delete('/references/{reference}/type/{type}/parents/{related?}', [ReferenceParentsController::class, 'delete'])
    ->name('references.parents.destroy');

/**************************************************************************
 * Reference Comments
 **************************************************************************/

Route::get('/references/{reference}/comments', [ReferenceCommentsController::class, 'index'])
    ->name('references.comments.index');
Route::post('/references/{reference}/task/{task}/comments', [ReferenceCommentsController::class, 'store'])
    ->name('references.comments.store');
Route::put('/references/{reference}/task/{task}/comments/{comment}', [ReferenceCommentsController::class, 'update'])
    ->name('references.comments.update');
Route::delete('/references/{reference}/task/{task}/comments/{comment}', [ReferenceCommentsController::class, 'destroy'])
    ->name('references.comments.destroy');

/**************************************************************************
 * Reference Content
 **************************************************************************/

Route::get('/references/{reference}/content', [ReferenceContentController::class, 'show'])
    ->name('references.content.show');
Route::put('/references/{reference}/content', [ReferenceContentController::class, 'update'])
    ->name('references.content.update')
    ->can('collaborate.corpus.reference.update');

/**************************************************************************
 * Reference Content Extracts
 **************************************************************************/

Route::get('/references/{reference}/content/extracts', [ReferenceContentExtractController::class, 'show'])
    ->name('references.content.extracts.show');

/**************************************************************************
 * Reference Meta Data
 **************************************************************************/
Route::post('/references/{reference}/meta/{relation}', [ReferenceMetadataController::class, 'store'])
    ->name('references.meta.store');

Route::delete('/references/bulk/meta/{relation}', [ReferenceMetadataController::class, 'destroyForBulk'])
    ->name('references.meta.bulk.destroy');

Route::delete('/references/{reference}/meta/{relation}/{related}', [ReferenceMetadataController::class, 'destroy'])
    ->name('references.meta.destroy');

Route::post('/references/{reference}/meta-drafts/{relation}/{related}', [ReferenceMetadataController::class, 'applyDrafts'])
    ->name('references.meta-drafts.apply');

Route::delete('/references/{reference}/meta-drafts/{relation}/{related}', [ReferenceMetadataController::class, 'destroyDraft'])
    ->name('references.meta-drafts.destroy');

Route::delete('/references/bulk/meta-drafts/{relation}', [ReferenceMetadataController::class, 'deleteDraftsForBulk'])
    ->name('references.meta-drafts.bulk.destroy');

Route::post('/references/bulk/meta-drafts/{relation}', [ReferenceMetadataController::class, 'applyDraftsForBulk'])
    ->name('references.meta-drafts.bulk.apply');

/**************************************************************************
 * Reference Requirement
 **************************************************************************/

Route::post('/references/{reference}/requirement', [ReferenceRequirementController::class, 'store'])
    ->name('references.requirement.store');

Route::put('/references/{reference}/requirement', [ReferenceRequirementController::class, 'update'])
    ->name('references.requirement.apply')
    ->can('collaborate.corpus.reference.requirement.apply');

Route::delete('/references/{reference}/requirement/draft/task/{task?}', [ReferenceRequirementController::class, 'destroy'])
    ->name('references.requirement.draft.delete');

Route::delete('/references/{reference}/requirement', [ReferenceRequirementController::class, 'forceDelete'])
    ->name('references.requirement.delete')
    ->can('collaborate.corpus.reference.requirement.delete-applied');

/**************************************************************************
 * Reference Summaries
 **************************************************************************/

Route::post('/references/{reference}/summary', [ReferenceSummaryController::class, 'store'])
    ->name('references.summary.store')
    ->can('collaborate.requirements.summary.draft.create');
Route::delete('/references/{reference}/summary', [ReferenceSummaryController::class, 'delete'])
    ->name('references.summary.destroy')
    ->can('collaborate.requirements.summary.delete');

Route::put('/references/{reference}/summary', [ReferenceSummaryDraftController::class, 'apply'])
    ->name('references.summary.draft.apply');
Route::put('/references/{reference}/summary/draft', [ReferenceSummaryDraftController::class, 'update'])
    ->name('references.summary.draft.update');
Route::delete('/references/{reference}/summary/draft', [ReferenceSummaryDraftController::class, 'delete'])
    ->name('references.summary.draft.destroy');

/**************************************************************************
 * References
 **************************************************************************/

Route::get('/work-expressions/{expression}/task/{task}/references', [ReferenceController::class, 'index'])
    ->name('work-expression.references.task.index');
Route::get('/work-expressions/{expression}/task/{task}/references/{reference}', [ReferenceController::class, 'show'])
    ->name('work-expression.references.task.show');
Route::get('/works/{work}/references', [ReferenceController::class, 'index'])
    ->name('works.references.index');

/**************************************************************************
 * Suggestions
 **************************************************************************/

Route::get('/reference/{reference}/suggest/assessment-items', [AssessmentItemSuggestionController::class, 'index'])
    ->name('reference.suggest.assessment-items');
Route::get('/reference/{reference}/suggest/categories', [CategorySuggestionController::class, 'index'])
    ->name('reference.suggest.categories');
Route::get('/reference/{reference}/suggest/context-questions', [ContextQuestionSuggestionController::class, 'index'])
    ->name('reference.suggest.context-questions');
Route::get('/reference/{reference}/suggest/action-areas', [ActionAreaSuggestionController::class, 'index'])
    ->name('reference.suggest.action-areas');
Route::get('/reference/{reference}/suggest/legal-domains', [LegalDomainSuggestionController::class, 'index'])
    ->name('reference.suggest.legal-domains');

/**************************************************************************
 * ToC Items
 **************************************************************************/
Route::get('/work-expressions/{expression}/toc-items/{item?}', [TocItemController::class, 'index'])
    ->name('work-expressions.toc-items.index');

Route::post('/work-expressions/{expression}/toc-items/bulk/insert-from-toc/{reference?}', [TocItemController::class, 'bulkInsertFromTocItem'])
    ->name('work-expressions.toc-items.bulk.insert-from-toc')
    ->can('collaborate.corpus.reference.create');
Route::post('/work-expressions/{expression}/toc-items/{item}/insert-from-toc/{reference?}', [TocItemController::class, 'insertFromTocItem'])
    ->name('work-expressions.toc-items.insert-from-toc')
    ->can('collaborate.corpus.reference.create');
Route::post('/work-expressions/{expression}/toc-items/{item}/update-from-toc/{reference}', [TocItemController::class, 'updateFromTocItem'])
    ->name('work-expressions.toc-items.update-from-toc')
    ->can('collaborate.corpus.reference.create');

/**************************************************************************
 * Work Expression Content
 **************************************************************************/

Route::get('/work-expressions/{expression}/content', [WorkExpressionContentController::class, 'show'])
    ->name('work-expressions.content.show');
Route::get('/work-expressions/{expression}/toc', [ReferenceController::class, 'toc'])
    ->name('work-expressions.toc.index');

/**************************************************************************
 * Works
 **************************************************************************/

Route::get('/works', [WorkController::class, 'index'])
    ->name('works.index');
