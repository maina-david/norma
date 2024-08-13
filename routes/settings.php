<?php

use App\Http\Controllers\Assess\My\Settings\AssessSetupStreamController;
use App\Http\Controllers\Auth\My\Settings\ForLibryoUserController;
use App\Http\Controllers\Auth\My\Settings\ForOrganisationUserController;
use App\Http\Controllers\Auth\My\Settings\ForOrganisationUserImportController;
use App\Http\Controllers\Auth\My\Settings\ForTeamUserController;
use App\Http\Controllers\Auth\My\Settings\PasswordResetController;
use App\Http\Controllers\Auth\My\Settings\SSOPageController;
use App\Http\Controllers\Auth\My\Settings\UserController;
use App\Http\Controllers\Compilation\My\Settings\ForLegalUpdateLibraryController;
use App\Http\Controllers\Compilation\My\Settings\ForLegalUpdateLibryoController;
use App\Http\Controllers\Compilation\My\Settings\ForLibraryLibraryController;
use App\Http\Controllers\Compilation\My\Settings\ForReferenceLibraryController;
use App\Http\Controllers\Compilation\My\Settings\GenericCompilationController;
use App\Http\Controllers\Compilation\My\Settings\LibraryController;
use App\Http\Controllers\Compilation\My\Settings\ManualCompilationController;
use App\Http\Controllers\Compilation\My\Settings\SessionLibraryController;
use App\Http\Controllers\Corpus\ForSelectorReferenceController;
use App\Http\Controllers\Corpus\ForSelectorWorkController;
use App\Http\Controllers\Corpus\My\Settings\ForLibraryWorkController;
use App\Http\Controllers\Corpus\My\Settings\ForManualCompilationWorkController;
use App\Http\Controllers\Customer\My\Settings\ForLibraryLibryoController;
use App\Http\Controllers\Customer\My\Settings\ForLibryoTeamController;
use App\Http\Controllers\Customer\My\Settings\ForOrganisationChildOrganisationController;
use App\Http\Controllers\Customer\My\Settings\ForOrganisationLibryoController;
use App\Http\Controllers\Customer\My\Settings\ForOrganisationTeamController;
use App\Http\Controllers\Customer\My\Settings\ForTeamLibryoController;
use App\Http\Controllers\Customer\My\Settings\ForUserLibryoController;
use App\Http\Controllers\Customer\My\Settings\ForUserOrganisationController;
use App\Http\Controllers\Customer\My\Settings\ForUserTeamController;
use App\Http\Controllers\Customer\My\Settings\LibryoController;
use App\Http\Controllers\Customer\My\Settings\LibryoRequirementsCollectionController;
use App\Http\Controllers\Customer\My\Settings\OrganisationController;
use App\Http\Controllers\Customer\My\Settings\OrganisationStreamController;
use App\Http\Controllers\Customer\My\Settings\TeamController;
use App\Http\Controllers\Dashboard\My\Settings\DashboardController;
use App\Http\Controllers\Geonames\Settings\LocationJsonController;
use App\Http\Controllers\Log\My\Settings\UserLifecycleActivitiesController;
use App\Http\Controllers\Notify\My\Settings\LegalUpdateController;
use App\Http\Controllers\Ontology\My\Settings\ForLibryoLegalDomainController;
use App\Http\Controllers\Ontology\My\Settings\LegalDomainController;
use App\Http\Controllers\Storage\My\Settings\FolderController;
use App\Http\Controllers\Storage\My\Settings\FolderStreamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'dashboard'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| User Imports
|--------------------------------------------------------------------------
*/

Route::middleware(['can:access.org.settings'])->group(function () {
    Route::get('/users/import/template', [ForOrganisationUserImportController::class, 'template'])
        ->name('users.for.organisation.import.template');

    Route::get('/users/import', [ForOrganisationUserImportController::class, 'create'])
        ->name('users.for.organisation.import.create');

    Route::post('/users/import', [ForOrganisationUserImportController::class, 'store'])
        ->name('users.for.organisation.import.store');
});

/*
|--------------------------------------------------------------------------
| Resource Routes
|--------------------------------------------------------------------------
*/
Route::get('/legal-domains', [LegalDomainController::class, 'index'])
    ->name('legal-domains.index');

Route::resources([
    'libraries' => LibraryController::class,
    'libryos' => LibryoController::class,
    'users' => UserController::class,
    'teams' => TeamController::class,
]);

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/
Route::get('/organisations', [OrganisationController::class, 'index'])
    ->name('organisations.index')
    ->middleware(['can:access.org.settings.all']);

Route::get('/organisations/{organisation}/edit', [OrganisationController::class, 'edit'])
    ->name('organisations.edit')
    ->middleware(['can:access.org.settings.all']);

Route::put('/organisations/{organisation}/edit', [OrganisationController::class, 'update'])
    ->name('organisations.update')
    ->middleware(['can:access.org.settings.all']);

Route::get('/organisations/create', [OrganisationController::class, 'create'])
    ->name('organisations.create')
    ->middleware(['can:access.org.settings.all']);

Route::post('/organisations', [OrganisationController::class, 'store'])
    ->name('organisations.store')
    ->middleware(['can:access.org.settings.all']);

Route::get('/organisations/{organisation}', [OrganisationController::class, 'show'])
    ->name('organisations.show')
    ->middleware(['can:access.org.settings.all']);

Route::delete('/organisations/{organisation}', [OrganisationController::class, 'destroy'])
    ->name('organisations.destroy')
    ->middleware(['can:access.org.settings.all']);

Route::post('/organisations/{organisation}/activate', [OrganisationController::class, 'activate'])
    ->name('organisations.activate')
    ->middleware(['can:access.org.settings,organisation']);

Route::post('/organisations/activate-all', [OrganisationController::class, 'activateAll'])
    ->name('organisations.activate.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/organisations/switcher/search', [OrganisationStreamController::class, 'switcherSearch'])
    ->name('organisations.switcher.search');

Route::post('/organisations/{organisation}/users/add', [ForOrganisationUserController::class, 'addUsers'])
    ->name('users.for.organisation.add')
    ->middleware(['can:access.org.settings.all']);

Route::post('/organisations/{organisation}/users/actions/all', [ForOrganisationUserController::class, 'actions'])
    ->name('users.for.organisation.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/organisations/{organisation}/modules', [OrganisationController::class, 'updateModules'])
    ->name('organisations.modules.update')
    ->middleware(['can:access.org.settings.all']);

Route::get('/organisations/{organisation}/modules', [OrganisationController::class, 'modules'])
    ->name('organisations.modules.index')
    ->middleware(['can:access.org.settings.all']);

/*
|--------------------------------------------------------------------------
| Users for organisation
|--------------------------------------------------------------------------
*/
Route::get('/organisations/{organisation}/users', [ForOrganisationUserController::class, 'index'])
    ->name('users.for.organisation.index')
    ->middleware(['can:access.org.settings.all']);

Route::get('/organisations/{organisation}/users/export', [ForOrganisationUserController::class, 'export'])
    ->name('users.for.organisation.export')
    ->middleware(['can:access.org.settings.all']);
/*
|--------------------------------------------------------------------------
| Teams for Organisation
|--------------------------------------------------------------------------
*/
Route::get('/organisations/{organisation}/teams', [ForOrganisationTeamController::class, 'index'])
    ->name('teams.for.organisation.index')
    ->middleware(['can:access.org.settings.all']);
/*
|--------------------------------------------------------------------------
| Libryos for Organisation
|--------------------------------------------------------------------------
*/
Route::get('/organisations/{organisation}/libryos', [ForOrganisationLibryoController::class, 'index'])
    ->name('libryos.for.organisation.index')
    ->middleware(['can:access.org.settings.all']);
/*
|--------------------------------------------------------------------------
| Teams for User
|--------------------------------------------------------------------------
*/
Route::get('/users/{user}/teams', [ForUserTeamController::class, 'indexForUser'])
    ->name('teams.for.user.index');

Route::post('/users/{user}/teams/actions/all', [ForUserTeamController::class, 'actions'])
    ->name('teams.for.user.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/users/{user}/teams/actions/{organisation}', [ForUserTeamController::class, 'actions'])
    ->name('teams.for.user.actions.organisation')
    ->middleware(['can:access.org.settings,organisation']);

Route::post('/users/{user}/teams/add', [ForUserTeamController::class, 'addTeams'])
    ->name('teams.for.user.add');

/*
|--------------------------------------------------------------------------
| Libryos for User
|--------------------------------------------------------------------------
*/
Route::get('/users/{user}/libryos', [ForUserLibryoController::class, 'index'])
    ->name('libryos.for.user.index');
/*
|--------------------------------------------------------------------------
| Organisations for Organisation
|--------------------------------------------------------------------------
*/
Route::get('/organisations/children/search', [ForOrganisationChildOrganisationController::class, 'availableChildren'])
    ->name('organisations.children.search');

Route::get('/organisations/{organisation}/children', [ForOrganisationChildOrganisationController::class, 'index'])
    ->name('child-organisations.for.organisation.index')
    ->middleware(['can:access.org.settings.all']);

Route::post('/organisations/{organisation}/children', [ForOrganisationChildOrganisationController::class, 'store'])
    ->name('child-organisations.for.organisation.store')
    ->middleware(['can:access.org.settings.all']);

Route::post('/organisations/{organisation}/children/actions', [ForOrganisationChildOrganisationController::class, 'actions'])
    ->name('child-organisations.for.organisation.actions')
    ->middleware(['can:access.org.settings']);
/*
|--------------------------------------------------------------------------
| Organisations for User
|--------------------------------------------------------------------------
*/
Route::get('/users/organisations/search', [ForUserOrganisationController::class, 'organisations'])
    ->name('organisations.for.user.search');

Route::get('/users/{user}/organisations', [ForUserOrganisationController::class, 'index'])
    ->name('organisations.for.user.index');

Route::post('/users/{user}/organisations/add', [ForUserOrganisationController::class, 'add'])
    ->name('organisations.for.user.add')
    ->middleware(['can:access.org.settings']);

Route::post('/users/{user}/organisations/actions', [ForUserOrganisationController::class, 'actions'])
    ->name('organisations.for.user.actions')
    ->middleware(['can:access.org.settings']);

/*
|--------------------------------------------------------------------------
| Teams for libryo
|--------------------------------------------------------------------------
*/
Route::get('/libryos/{libryo}/teams', [ForLibryoTeamController::class, 'index'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('teams.for.libryo.index');

Route::post('/libryos/{libryo}/teams/actions/all', [ForLibryoTeamController::class, 'actions'])
    ->name('teams.for.libryo.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libryos/{libryo}/teams/actions/{organisation}', [ForLibryoTeamController::class, 'actions'])
    ->name('teams.for.libryo.actions.organisation')
    ->middleware(['can:access.org.settings,organisation']);

Route::post('/libryos/{libryo}/teams/add', [ForLibryoTeamController::class, 'addTeams'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('teams.for.libryo.add');
/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::post('/users/actions/all', [UserController::class, 'actionsForAll'])
    ->name('users.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/users/actions/{organisation}', [UserController::class, 'actionsForOrganisation'])
    ->name('users.actions.organisation')
    ->middleware(['can:access.org.settings,organisation']);

/*
|--------------------------------------------------------------------------
| Users for libryo
|--------------------------------------------------------------------------
*/
Route::get('/libryos/{libryo}/users', [ForLibryoUserController::class, 'index'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('users.for.libryo.index');

/*
|--------------------------------------------------------------------------
| Legal Domains for libryo
|--------------------------------------------------------------------------
*/
Route::get('/libryos/{libryo}/legal-domains', [ForLibryoLegalDomainController::class, 'index'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('libryos.compilation.legal-domains.index');

Route::post('/libryos/{libryo}/legal-domains/actions', [ForLibryoLegalDomainController::class, 'actions'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('legal-domains.for.libryo.actions');

Route::post('/libryos/{libryo}/legal-domains/add', [ForLibryoLegalDomainController::class, 'add'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('legal-domains.for.libryo.add');

/*
|--------------------------------------------------------------------------
| Libryos for team
|--------------------------------------------------------------------------
*/
Route::get('/teams/{team}/libryos', [ForTeamLibryoController::class, 'index'])
    ->middleware(['can:manageInSettings,team'])
    ->name('libryos.for.team.index');

Route::post('/teams/{team}/libryos/actions/all', [ForTeamLibryoController::class, 'actions'])
    ->name('libryos.for.team.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/teams/{team}/libryos/actions/{organisation}', [ForTeamLibryoController::class, 'actions'])
    ->name('libryos.for.team.actions.organisation')
    ->middleware(['can:access.org.settings,organisation']);

Route::post('/teams/{team}/libryos/add', [ForTeamLibryoController::class, 'addLibryos'])
    ->middleware(['can:manageInSettings,team'])
    ->name('libryos.for.team.add');

/*
|--------------------------------------------------------------------------
| Libryos for library
|--------------------------------------------------------------------------
*/
Route::get('/libraries/{library}/libryos', [ForLibraryLibryoController::class, 'index'])
    ->name('libryos.for.library.index');

Route::get('/libraries/{library}/requirements', [ForLibraryWorkController::class, 'index'])
    ->name('works.for.library.index')
    ->middleware(['can:access.org.settings.all']);

/*
|--------------------------------------------------------------------------
| Users in team
|--------------------------------------------------------------------------
*/
Route::get('/teams/{team}/users', [ForTeamUserController::class, 'index'])
    ->middleware(['can:manageInSettings,team'])
    ->name('users.for.team.index');

Route::post('/teams/{team}/users/actions/all', [ForTeamUserController::class, 'actions'])
    ->name('users.for.team.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/teams/{team}/users/actions/{organisation}', [ForTeamUserController::class, 'actions'])
    ->name('users.for.team.actions.organisation')
    ->middleware(['can:access.org.settings,organisation']);

Route::post('/teams/{team}/users/add', [ForTeamUserController::class, 'addUsers'])
    ->middleware(['can:manageInSettings,team'])
    ->name('users.for.team.add');

/*
|--------------------------------------------------------------------------
| Libryos
|--------------------------------------------------------------------------
*/

Route::post('/libryos/{libryo}/clone', [LibryoController::class, 'clone'])
    ->name('libryo.clone');

Route::get('/libryos/legal-update/{update}', [ForLegalUpdateLibryoController::class, 'index'])
    ->name('compilation.libryos.for.legal-update.index')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libryos/actions/all', [LibryoController::class, 'actions'])
    ->name('libryos.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libryos/actions/{organisation}', [LibryoController::class, 'actions'])
    ->name('libryos.actions.organisation')
    ->middleware(['can:access.org.settings,organisation']);

Route::post('/libryos/{libryo}/modules', [LibryoController::class, 'updateModules'])
    ->name('libryos.modules.update')
    ->middleware(['can:access.org.settings.all']);

Route::get('/libryos/{libryo}/modules', [LibryoController::class, 'modules'])
    ->name('libryos.modules.index')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libryos/{libryo}/compilation-settings', [LibryoController::class, 'updateCompilationSettings'])
    ->name('libryos.compilation-settings.update')
    ->middleware(['can:access.org.settings.all']);

Route::get('/libryos/{libryo}/compilation-settings', [LibryoController::class, 'compilationSettings'])
    ->name('libryos.compilation-settings.index')
    ->middleware(['can:access.org.settings.all']);
/*
|--------------------------------------------------------------------------
| Libraries
|--------------------------------------------------------------------------
*/

Route::get('/libraries/session/{key}', [SessionLibraryController::class, 'loadedLibraries'])
    ->name('compilation.libraries.session')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libraries/session/references/{key}', [SessionLibraryController::class, 'addByReferences'])
    ->name('compilation.libraries.session.add.by.references')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libraries/session/{key}', [SessionLibraryController::class, 'addLibrary'])
    ->name('compilation.libraries.session.add')
    ->middleware(['can:access.org.settings.all']);

Route::delete('/libraries/session/{key}', [SessionLibraryController::class, 'clearLibraries'])
    ->name('compilation.libraries.session.remove')
    ->middleware(['can:access.org.settings.all']);

Route::get('/libraries/reference/{reference}', [ForReferenceLibraryController::class, 'index'])
    ->name('compilation.library.for.reference.index')
    ->middleware(['can:access.org.settings.all']);

Route::get('/libraries/legal-update/{update}', [ForLegalUpdateLibraryController::class, 'index'])
    ->name('compilation.library.for.legal-update.index')
    ->middleware(['can:access.org.settings.all']);
/*
|--------------------------------------------------------------------------
| Library Children and Parents
|--------------------------------------------------------------------------
*/

Route::get('/libraries/{library}/{relation}', [ForLibraryLibraryController::class, 'index'])
    ->name('children-parents.for.library.index');

Route::post('/libraries/{library}/{relation}/actions/all', [ForLibraryLibraryController::class, 'actions'])
    ->name('children-parents.for.library.actions.all')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libraries/{library}/{relation}/actions/{organisation}', [ForLibraryLibraryController::class, 'actions'])
    ->name('children-parents.for.library.actions.organisation')
    ->middleware(['can:access.org.settings,organisation']);

Route::post('/libraries/{library}/{relation}', [ForLibraryLibraryController::class, 'addLibraries'])
    ->name('children-parents.for.library.add');

Route::get('/libraries/{library}/generic/compile', [GenericCompilationController::class, 'showCompile'])
    ->name('compilation.generic.compile.show')
    ->middleware(['can:access.org.settings.all']);

Route::get('/libraries/{library}/generic/decompile', [GenericCompilationController::class, 'showDecompile'])
    ->name('compilation.generic.decompile.show')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libraries/{library}/generic/compile', [GenericCompilationController::class, 'compile'])
    ->name('compilation.generic.compile')
    ->middleware(['can:access.org.settings.all']);

Route::post('/libraries/{library}/generic/decompile', [GenericCompilationController::class, 'decompile'])
    ->name('compilation.generic.decompile')
    ->middleware(['can:access.org.settings.all']);

/*
|--------------------------------------------------------------------------
| Manual Compilation
|--------------------------------------------------------------------------
*/
Route::get('/compilation', [ManualCompilationController::class, 'compilation'])
    ->name('compilation.manual-compilation')
    ->middleware(['can:access.org.settings.all']);

Route::post('/compilation/add-work', [ManualCompilationController::class, 'addWork'])
    ->name('compilation.works.for.manual.compilation.add')
    ->middleware(['can:access.org.settings.all']);

Route::get('/works/compilation', [ForManualCompilationWorkController::class, 'index'])
    ->name('compilation.works.for.manual.compilation')
    ->middleware(['can:access.org.settings.all']);

Route::delete('/works/compilation', [ManualCompilationController::class, 'clearWorks'])
    ->name('compilation.works.for.manual.compilation.remove')
    ->middleware(['can:access.org.settings.all']);

Route::get('/compilation/reference/{reference}/add', [ManualCompilationController::class, 'compileReference'])
    ->name('compilation.manual.reference.add')
    ->middleware(['can:access.org.settings.all']);

Route::get('/compilation/reference/{reference}/remove', [ManualCompilationController::class, 'decompileReference'])
    ->name('compilation.manual.reference.remove')
    ->middleware(['can:access.org.settings.all']);

Route::get('/compilation/work/{work}/add', [ManualCompilationController::class, 'compileWork'])
    ->name('compilation.manual.work.add')
    ->middleware(['can:access.org.settings.all']);

Route::get('/compilation/work/{work}/remove', [ManualCompilationController::class, 'decompileWork'])
    ->name('compilation.manual.work.remove')
    ->middleware(['can:access.org.settings.all']);

Route::get('/compilation/works/add', [ManualCompilationController::class, 'compileAllWorks'])
    ->name('compilation.manual.all.works.add')
    ->middleware(['can:access.org.settings.all']);

Route::get('/compilation/works/remove', [ManualCompilationController::class, 'decompileAllWorks'])
    ->name('compilation.manual.all.works.remove')
    ->middleware(['can:access.org.settings.all']);

/*
|--------------------------------------------------------------------------
| Works
|--------------------------------------------------------------------------
*/
Route::get('/works/selector', [ForSelectorWorkController::class, 'index'])
    ->name('corpus.works.for.selector.index')
    ->middleware(['can:access.org.settings.all']);

/*
|--------------------------------------------------------------------------
| References
|--------------------------------------------------------------------------
*/
Route::get('/references/selector/content/{reference}', [ForSelectorReferenceController::class, 'cachedContent'])
    ->name('corpus.reference.for.selector.content')
    ->middleware(['can:access.org.settings.all']);
Route::get('/references/selector/{work}', [ForSelectorReferenceController::class, 'index'])
    ->name('corpus.reference.for.selector.index')
    ->middleware(['can:access.org.settings.all']);
/*
|--------------------------------------------------------------------------
| Locations
|--------------------------------------------------------------------------
*/
Route::get('/locations/json', [LocationJsonController::class, 'index'])
    ->name('locations.json.index');

/*
|--------------------------------------------------------------------------
| User Lifecycle Activities
|--------------------------------------------------------------------------
*/
Route::get('/users/{user}/lifecycle-activities', [UserLifecycleActivitiesController::class, 'forUser'])
    ->name('users.lifecycle.activities');

/*
|--------------------------------------------------------------------------
| Impersonation
|--------------------------------------------------------------------------
*/
Route::get('/users/{user}/impersonate', [UserController::class, 'impersonate'])
    ->name('users.impersonate')
    ->middleware('can:my.users.impersonate');

/*
|--------------------------------------------------------------------------
| Password Reset
|--------------------------------------------------------------------------
*/
Route::post('/users/{user}/password-reset', [PasswordResetController::class, 'store'])
    ->name('users.password-reset')
    ->middleware('can:my.users.password.reset');

/*
|--------------------------------------------------------------------------
| Assess Setup
|--------------------------------------------------------------------------
*/
Route::get('/libryos/{libryo}/assess/setup', [AssessSetupStreamController::class, 'setupForLibryo'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('assess.setup.for.libryo');
Route::get('/organisations/{organisation}/assess/setup', [AssessSetupStreamController::class, 'setupForOrganisation'])
    ->name('assess.setup.for.organisation')
    ->middleware(['can:access.org.settings,organisation']);
Route::get('/libryos/{libryo}/assess/unused-items', [AssessSetupStreamController::class, 'unusedItemsForLibryo'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('assess.setup.unused.items.for.libryo');
Route::get('/libryos/{libryo}/assess/used-items', [AssessSetupStreamController::class, 'usedItemsForLibryo'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('assess.setup.used.items.for.libryo');
Route::post('/libryos/{libryo}/assess/setup/actions', [AssessSetupStreamController::class, 'actionsForLibryo'])
    ->middleware(['can:manageInSettings,libryo'])
    ->name('assess.setup.actions.for.libryo');
Route::post('/organisations/{organisation}/assess/setup/activate', [AssessSetupStreamController::class, 'activateUnusedItemsForOrganisation'])
    ->name('assess.setup.for.organisation.activate.items')
    ->middleware(['can:access.org.settings,organisation']);

/*
|--------------------------------------------------------------------------
| Drives Setup
|--------------------------------------------------------------------------
*/
Route::get('/drives/{type?}/{folder?}', [FolderController::class, 'setup'])
    ->name('drives.setup');
Route::get('/drives/folders/{folder}/visible/{hideOrShow}', [FolderStreamController::class, 'hideOrShowLibryoCreatedFolders'])
    ->name('drives.setup.hide-or-show.folder')
    ->middleware(['can:hideOrShowInOrgSettings,folder']);

Route::get('/folders/global/{folder?}', [FolderStreamController::class, 'indexGlobalFolder'])
    ->name('folder.global.index')
    ->middleware(['can:my.storage.file.global.manage']);
Route::post('/folders/global/{parentFolder?}', [FolderStreamController::class, 'storeGlobalFolder'])
    ->name('folder.global.store')
    ->middleware(['can:my.storage.file.global.manage']);
Route::put('/folders/global/{folder}', [FolderStreamController::class, 'updateGlobalFolder'])
    ->name('folder.global.update')
    ->middleware(['can:my.storage.file.global.manage']);
Route::delete('/folders/global/{folder}', [FolderStreamController::class, 'destroyGlobalFolder'])
    ->name('folder.global.destroy')
    ->middleware(['can:my.storage.file.global.manage']);

Route::delete('/files/global/{file}', [FolderStreamController::class, 'destroyGlobalFile'])
    ->name('files.global.destroy')
    ->middleware(['can:my.storage.file.global.manage']);

Route::post('/files/global/{folder}', [FolderStreamController::class, 'storeGlobalFile'])
    ->name('files.global.post')
    ->middleware(['can:my.storage.file.global.manage']);

Route::get('/folders/organisation/{folderType}/{folder?}', [FolderStreamController::class, 'indexOrganisationFolder'])
    ->name('folder.index.for.organisation');
Route::post('/folders/organisation/{parentFolder?}', [FolderStreamController::class, 'storeOrganisationFolder'])
    ->name('folder.store.for.organisation');
Route::put('/folders/{folder}/organisation/{parentFolder?}', [FolderStreamController::class, 'updateOrganisationFolder'])
    ->name('folder.update.for.organisation');
Route::delete('/folders/{folder}/organisation', [FolderStreamController::class, 'destroyOrganisationFolder'])
    ->name('folder.destroy.for.organisation');

/*
|--------------------------------------------------------------------------
| Legal Updates
|--------------------------------------------------------------------------
*/

Route::get('/legal-updates', [LegalUpdateController::class, 'index'])
    ->name('legal-updates.index')
    ->middleware(['can:my.notify.legal-update.viewAny']);

Route::get('/legal-updates/{update}', [LegalUpdateController::class, 'show'])
    ->name('legal-updates.show')
    ->middleware(['can:my.notify.legal-update.view']);

Route::post('/legal-updates/{update}/libraries', [LegalUpdateController::class, 'addByLibraries'])
    ->name('legal-updates.libraries.store')
    ->middleware(['can:my.notify.legal-update.update']);

Route::post('/legal-updates/{update}/references', [LegalUpdateController::class, 'addByReferences'])
    ->name('legal-updates.references.store')
    ->middleware(['can:my.notify.legal-update.update']);

Route::post('/legal-updates/{update}/libryos', [LegalUpdateController::class, 'addByLibryos'])
    ->name('legal-updates.libryos.store')
    ->middleware(['can:my.notify.legal-update.update']);

Route::post('/legal-updates/{update}/actions', [LegalUpdateController::class, 'actions'])
    ->name('legal-updates.actions')
    ->middleware(['can:my.notify.legal-update.update']);

/*
|--------------------------------------------------------------------------
| RequirementsCollections
|--------------------------------------------------------------------------
*/

Route::group(['as' => 'libryos.compilation.requirements-collections.'], function () {
    Route::get('/libryos/{libryo}/collections', [LibryoRequirementsCollectionController::class, 'index'])
        ->name('index');
    Route::delete('/libryos/{libryo}/collections/{collection}', [LibryoRequirementsCollectionController::class, 'destroyPivot'])
        ->name('destroy');
    Route::get('/libryos/{libryo}/collections/create', [LibryoRequirementsCollectionController::class, 'create'])
        ->name('create');
    Route::post('/libryos/{libryo}/collections', [LibryoRequirementsCollectionController::class, 'storePivot'])
        ->name('store');
});

/*
|--------------------------------------------------------------------------
| SSO Routes
|--------------------------------------------------------------------------
*/
Route::get('/sso', [SSOPageController::class, 'edit'])
    ->name('sso.edit');
Route::put('/sso', [SSOPageController::class, 'update'])
    ->name('sso.update');
