<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Enums\Application\ApplicationType;
use App\Exports\Customer\Settings\UserImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\UsesCrudViews;
use App\Http\Requests\Auth\UserImportRequest;
use App\Imports\Customer\Settings\UserImport;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use App\Services\Auth\UserLifecycleService;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Customer\OrganisationUserStore;
use App\Stores\Customer\TeamUserStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ForOrganisationUserImportController extends Controller
{
    use UsesCrudViews;

    /**
     * @param OrganisationUserStore $organisationUserStore
     */
    public function __construct(
        protected OrganisationUserStore $organisationUserStore,
    ) {
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resource(): string
    {
        return User::class;
    }

    /**
     * Get the path to the common form that is to be used.
     *
     * @return string
     */
    protected function getFormView(): string
    {
        return 'partials.auth.my.user.import';
    }

    /**
     * Send the form to import users.
     *
     * @return View
     */
    public function create(): View
    {
        $organisation = app(ActiveOrganisationManager::class)->getActive();

        abort_unless((bool) $organisation, 404);

        return $this->getView('create')->with([
            'application' => ApplicationType::my(),
            'appLayout' => 'layouts.settings',
            'organisation' => $organisation,
            'permission' => '',
            'form' => $this->getFormView(),
            'langFile' => 'auth.user.import',
            'baseRoute' => 'my.settings.users.for.organisation.import',
            'enctype' => 'multipart/form-data',
        ]);
    }

    /**
     * Import the given users.
     *
     * @param UserImportRequest     $request
     * @param UserLifecycleService  $lifecycleService
     * @param TeamUserStore         $teamStore
     * @param OrganisationUserStore $orgStore
     *
     * @return RedirectResponse
     */
    public function store(
        UserImportRequest $request,
        UserLifecycleService $lifecycleService,
        TeamUserStore $teamStore,
        OrganisationUserStore $orgStore
    ): RedirectResponse {
        $organisation = app(ActiveOrganisationManager::class)->getActive();

        abort_unless((bool) $organisation, 404);

        /** @var Collection<int, Team> $teams */
        $teams = Team::whereKey($request->get('team_id'))->get();

        /** @var UploadedFile $file */
        $file = $request->file('users');

        /** @var Role $role */
        $role = Role::where('title', 'My Users')->where('app', 'my')->first('id');

        /** @var Organisation $organisation */
        $import = new UserImport(
            $organisation,
            $teams,
            $lifecycleService,
            $teamStore,
            $orgStore,
            $role
        );

        Excel::import($import, $file);

        Session::flash('flash.message', __('notifications.successfully_imported'));

        return redirect()->to(route('my.settings.users.index'));
    }

    /**
     * Export the users of the organisation.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     *
     * @return BinaryFileResponse
     */
    public function template(): BinaryFileResponse
    {
        return Excel::download(new UserImportTemplateExport(), 'Import Users Template.xlsx');
    }
}
