<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Stores\Customer\OrganisationUserStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ForUserOrganisationController extends Controller
{
    use PerformsActions;

    /**
     * @param Request $request
     * @param User    $user
     *
     * @return Response
     */
    public function index(Request $request, User $user): Response
    {
        $baseQuery = Organisation::whereRelation('users', 'id', $user->id);

        /** @var User $authUser */
        $authUser = $request->user();

        if (!$authUser->canManageAllOrganisations()) {
            $baseQuery->userIsOrganisationAdmin($authUser);
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.organisation.for-user',
            'target' => 'settings-organisations-for-user-' . $user->id,
            'baseQuery' => $baseQuery,
            'user' => $user,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the organisations.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function organisations(Request $request): JsonResponse
    {
        abort_unless($request->wantsJson(), 404);
        $baseQuery = (new Organisation())->newQuery();

        /** @var User $user */
        $user = Auth::user();
        if (!$user->canManageAllOrganisations()) {
            $baseQuery->userIsOrganisationAdmin($user);
        }

        $json = $baseQuery
            ->filter($request->all())
            ->get(['id', 'title'])
            ->toArray();

        return response()->json($json);
    }

    /**
     * Add organisations to the selected user.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return RedirectResponse
     */
    public function add(Request $request, User $user): RedirectResponse
    {
        $baseQuery = (new Organisation())->newQuery();

        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser->canManageAllOrganisations()) {
            $baseQuery->userIsOrganisationAdmin($authUser);
        }

        /** @var Collection<Organisation> */
        $organisations = $baseQuery->whereKey($request->input('organisations', []))->get(['id']);

        app(OrganisationUserStore::class)->attachOrganisations($user, $organisations);

        Session::flash('flash.message', __('actions.success'));

        return redirect()->route('my.settings.organisations.for.user.index', ['user' => $user->id]);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, User $user): RedirectResponse
    {
        $actionName = $this->validateActionName($request);
        /** @var \Illuminate\Database\Eloquent\Collection<Organisation> */
        $collection = $this->filterActionInput($request, Organisation::class);

        $flashMessage = $this->performAction($actionName, $user, $collection);

        Session::flash('flash.message', $flashMessage);

        return redirect()->route('my.settings.organisations.for.user.index', ['user' => $user->id]);
    }

    /**
     * Execute the given action.
     *
     * @param string                   $action
     * @param User                     $user
     * @param Collection<Organisation> $organisations
     *
     * @return string
     */
    private function performAction(string $action, User $user, Collection $organisations): string
    {
        $flashMessage = '';
        switch ($action) {
            case 'remove_from_user':
                app(OrganisationUserStore::class)->detachOrganisations($user, $organisations);
                /** @var string $flashMessage */
                $flashMessage = __('actions.success');
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        return $flashMessage;
    }
}
