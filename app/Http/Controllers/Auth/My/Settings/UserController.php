<?php

namespace App\Http\Controllers\Auth\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DecodesHashids;
use App\Http\Controllers\Traits\PerformsActions;
use App\Http\Requests\Auth\SettingsUserRequest;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Services\Auth\ImpersonationManager;
use App\Services\Auth\UserLifecycleService;
use App\Services\Customer\ActiveOrganisationManager;
use App\Stores\Customer\OrganisationUserStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Throwable;

class UserController extends Controller
{
    use PerformsActions;
    use DecodesHashids;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     * @param UserLifecycleService      $userLifecycleService
     * @param ImpersonationManager      $impersonationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
        protected UserLifecycleService $userLifecycleService,
        protected ImpersonationManager $impersonationManager
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $organisation = null;
        if ($this->activeOrganisationManager->isSingleOrgMode()) {
            /** @var Organisation $organisation */
            $organisation = $this->activeOrganisationManager->getActive();
            $baseQuery = $organisation->users();
        } else {
            /** @var User $user */
            $user = Auth::user();
            $baseQuery = $user->canManageAllOrganisations()
                ? (new User())->newQuery()->with('organisations')
                // @codeCoverageIgnoreStart
                : null;
            // @codeCoverageIgnoreEnd
        }

        if ($request->wantsJson()) {
            // @phpstan-ignore-next-line
            $json = $baseQuery->filter($request->all())
                ->get(['id', 'sname', 'fname', 'email'])
                ->map(fn ($u) => ['id' => $u->id, 'title' => $u->full_name . ' (' . $u->email . ')'])
                ->toArray();

            return response()->json($json);
        }

        /** @var View $view */
        $view = view('pages.auth.my.user.settings.index', ['baseQuery' => $baseQuery, 'organisation' => $organisation]);

        return $view;
    }

    /**
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return RedirectResponse
     */
    public function actionsForOrganisation(Request $request, Organisation $organisation): RedirectResponse
    {
        return $this->actions($request, $organisation);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function actionsForAll(Request $request): RedirectResponse
    {
        return $this->actions($request);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request                $request
     * @param Organisation|null|null $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, ?Organisation $organisation = null): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var Collection<User> */
        $users = $this->filterActionInput($request, User::class);
        $users->load('organisations');

        $flashMessage = '';
        foreach ($users as $user) {
            if ($organisation && !$user->organisations->contains($organisation)) {
                abort(403);
            }
            $flashMessage = $this->performAction($actionName, $user, $organisation);
        }

        Session::flash('flash.message', $flashMessage);

        return back();
    }

    /**
     * @param string            $action
     * @param User              $user
     * @param Organisation|null $organisation
     *
     * @return string
     */
    private function performAction(string $action, User $user, ?Organisation $organisation = null): string
    {
        /** @var User */
        $currentUser = Auth::user();

        /** @var string $flashMessage */
        $flashMessage = '';
        switch ($action) {
            case 'activate':
                if (!$user->active) {
                    $this->userLifecycleService->activate($user);
                }
                /** @var string $flashMessage */
                $flashMessage = __('auth.user.successfully_activated');
                break;
            case 'deactivate':
                // don't let people deactivate themselves
                if ($user->id !== $currentUser->id) {
                    $this->userLifecycleService->deactivate($user);
                }
                /** @var string $flashMessage */
                $flashMessage = __('auth.user.successfully_deactivated');
                break;
            case 'resend_welcome_email':
                $this->userLifecycleService->reInvite($user);
                /** @var string $flashMessage */
                $flashMessage = __('auth.user.successfully_sent_welcome_email');
                break;
            case 'make_org_admin':
                // action should only be called in context of organisation
                /** @var Organisation $organisation */
                $user->organisations()->updateExistingPivot($organisation->id, ['is_admin' => true]);
                /** @var string $flashMessage */
                $flashMessage = __('auth.user.successfully_made_admin');
                break;
            case 'remove_org_admin':
                // action should only be called in context of organisation
                /** @var Organisation $organisation */
                // don't let people remove admin from themselves - otherwise won't have access to settings anymore
                if ($user->id !== $currentUser->id) {
                    $user->organisations()->updateExistingPivot($organisation->id, ['is_admin' => false]);
                }
                /** @var string $flashMessage */
                $flashMessage = __('auth.user.successfully_removed_admin');
                break;
            case 'delete_account':
                // don't let people delete their own accounts
                if ($user->id !== $currentUser->id) {
                    $user->anonymizeAndDestroyAccount();
                }
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
     * @param Request $request
     * @param string  $user
     *
     * @return View
     */
    public function show(Request $request, string $user): View
    {
        if (is_numeric($user)) {
            // @codeCoverageIgnoreStart
            abort(403);
            // @codeCoverageIgnoreEnd
        }
        /** @var User $user */
        $user = $this->decodeHash($user, User::class);
        $this->authorize('manageInSettings', $user);
        $user->user_roles = $user->roles()->my()->pluck('id')->toArray();

        $rolesQuery = preg_match('/@norma.com|@erm.com/', $user->email ?? '') === 1 ? Role::my() : Role::my()->where('title', '!=', 'My Super Users');

        /** @var View $view */
        $view = view('pages.auth.my.user.settings.show', [
            'user' => $user,
            'roles' => $rolesQuery->pluck('title', 'id')->toArray(),
        ]);

        return $view;
    }

    /**
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function impersonate(User $user): RedirectResponse
    {
        /** @var User */
        $currentUser = Auth::user();
        $this->impersonationManager->take($currentUser, $user);

        return redirect()->route('my.dashboard');
    }

    /**
     * Create a new user.
     *
     * @return View
     */
    public function create(): View
    {
        abort_unless((bool) app(ActiveOrganisationManager::class)->getActive(), 404);

        /** @var View */
        return view('pages.auth.my.user.settings.create', [
            'roles' => Role::my()->where('title', '!=', 'My Super Users')->pluck('title', 'id')->toArray(),
        ]);
    }

    /**
     * Create a new user.
     *
     * @param SettingsUserRequest $request
     *
     * @throws Throwable
     *
     * @return RedirectResponse
     */
    public function store(SettingsUserRequest $request): RedirectResponse
    {
        $organisation = app(ActiveOrganisationManager::class)->getActive();

        abort_unless((bool) $organisation, 404);
        /** @var Organisation $organisation */
        $data = $request->validated();
        $data['password'] = Hash::make(Str::random());

        $roles = isset($data['user_roles']) && $request->user()?->can('access.org.settings.all')
            ? $data['user_roles']
            : Role::where('title', 'My Users')->where('app', 'my')->pluck('id')->toArray();

        unset($data['user_roles']);

        $user = DB::transaction(function () use ($organisation, $roles, $data) {
            $user = User::create($data);

            if (!empty($roles)) {
                $user->updateMyRoles($roles);
            }

            /** @var Collection<User> $userCollection */
            $userCollection = (new User())->newCollection([$user]);

            app(OrganisationUserStore::class)->attachUsers($organisation, $userCollection);

            return $user;
        });

        app(UserLifecycleService::class)->invite($user->refresh());

        Session::flash('flash.message', __('auth.user.created_successfully'));

        return redirect()->route('my.settings.users.show', ['user' => $user->hash_id]);
    }

    /**
     * Edit the current user.
     *
     * @param SettingsUserRequest $request
     * @param User                $user
     *
     * @throws Throwable
     *
     * @return RedirectResponse
     */
    public function update(SettingsUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manageInSettings', $user);
        $data = $request->validated();
        $roles = collect($data['user_roles'] ?? [])->unique()->values();
        unset($data['user_roles']);

        DB::transaction(function () use ($request, $roles, $data, $user) {
            $user->update($data);

            if ($request->user()?->can('access.org.settings.all')) {
                $user->updateMyRoles($roles->toArray());
            }
        });

        Session::flash('flash.message', __('auth.user.updated_successfully'));

        return redirect()->route('my.settings.users.show', ['user' => $user->hash_id]);
    }
}
