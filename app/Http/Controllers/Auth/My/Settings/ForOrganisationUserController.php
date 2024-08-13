<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Actions\Customer\Organisation\AddUsers;
use App\Exports\Customer\Settings\UserExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Services\Auth\UserLifecycleService;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Customer\OrganisationUserStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ForOrganisationUserController extends Controller
{
    use PerformsActions;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
        protected OrganisationUserStore $organisationUserStore,
    ) {
    }

    /**
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function index(Organisation $organisation): Response
    {
        if ($org = $this->activeOrganisationManager->getActive()) {
            // @codeCoverageIgnoreStart
            $baseQuery = $organisation->users()
                ->getQuery();
        // @codeCoverageIgnoreEnd
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? $organisation->users()->getQuery()
                // @codeCoverageIgnoreStart
                : User::whereKey(0);
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.auth.my.user.for-organisation',
            'target' => 'settings-users-for-organisation-' . $organisation->id,
            'baseQuery' => $baseQuery,
            'org' => $organisation,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Organisation $organisation): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        /** @var Collection<User> */
        $users = $this->filterActionInput($request, User::class);

        $flashMessage = $this->performAction($actionName, $organisation, $users);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.users.for.organisation.index', ['organisation' => $organisation->id]));

        return $response;
    }

    /**
     * @param string           $action
     * @param Organisation     $organisation
     * @param Collection<User> $users
     *
     * @return string
     */
    private function performAction(string $action, Organisation $organisation, Collection $users): string
    {
        /** @var User */
        $currentUser = Auth::user();

        /** @var string $flashMessage */
        $flashMessage = '';
        switch ($action) {
            case 'remove_from_organisation':
                $this->organisationUserStore->detachUsers($organisation, $users);
                /** @var string $flashMessage */
                $flashMessage = __('actions.success');
                break;
            case 'resend_welcome_email':
                $service = app(UserLifecycleService::class);
                $users->each(function ($user) use ($service) {
                    /** @var User $user */
                    $service->reInvite($user);
                });
                $flashMessage = __('auth.user.successfully_sent_welcome_email');
                break;
            case 'delete_account':
                $users->each(callback: function ($user) use ($currentUser) {
                    // don't let people delete their own accounts
                    /** @var User $user */
                    if ($user->id !== $currentUser->id) {
                        $user->anonymizeAndDestroyAccount();
                    }
                });
                /** @var string $flashMessage */
                $flashMessage = __('auth.user.successfully_deleted_user_account');
                break;

                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        return $flashMessage;
    }

    /**
     * @return RedirectResponse
     */
    public function addUsers(Request $request, Organisation $organisation): RedirectResponse
    {
        $userIds = $request->input('users', []);

        AddUsers::run($organisation, $userIds);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.users.for.organisation.index', ['organisation' => $organisation->id]));

        return $response;
    }

    /**
     * Export the users of the organisation.
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     *
     * @return BinaryFileResponse
     */
    public function export(Request $request, Organisation $organisation): BinaryFileResponse
    {
        return Excel::download(new UserExport($organisation), "{$organisation->title} - Users.xlsx");
    }
}
