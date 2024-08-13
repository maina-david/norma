<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ForOrganisationChildOrganisationController extends Controller
{
    use PerformsActions;

    /**
     * Get the base query.
     *
     * @return Builder
     */
    protected function baseQuery(): Builder
    {
        $baseQuery = (new Organisation())->newQuery();

        /** @var User $user */
        $user = Auth::user();

        if (!$user->canManageAllOrganisations()) {
            // @codeCoverageIgnoreStart
            $baseQuery->userIsOrganisationAdmin($user);
            // @codeCoverageIgnoreEnd
        }

        /** @var Builder */
        return $baseQuery;
    }

    /**
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function index(Request $request, Organisation $organisation): Response
    {
        $baseQuery = Organisation::where('parent_id', $organisation->id)->filter($request->all());

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.organisation.for-organisation',
            'target' => 'settings-children-for-organisation-' . $organisation->id,
            'baseQuery' => $baseQuery,
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the organisations that are children and not part of any parent.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function availableChildren(Request $request): JsonResponse
    {
        $this->abortNotJson();

        $json = $this->baseQuery()
            ->filter($request->all())
            ->where('is_parent', false)
            ->whereNull('parent_id')
            ->get(['id', 'title'])
            ->toArray();

        return response()->json($json);
    }

    /**
     * Add child organisations.
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return RedirectResponse
     */
    public function store(Request $request, Organisation $organisation): RedirectResponse
    {
        $this->baseQuery()
            ->whereKey($request->input('organisations', []))
            ->where('is_parent', false)
            ->whereNull('parent_id')
            ->get(['id', 'is_parent', 'parent_id'])
            ->each(function ($org) use ($organisation) {
                $org->update(['parent_id' => $organisation->id]);
            });

        Session::flash('flash.message', __('actions.success'));

        return redirect()
            ->route('my.settings.child-organisations.for.organisation.index', ['organisation' => $organisation->id]);
    }

    /**
     * Perform a given action on a given list of organisations.
     *
     * @param Request      $request
     * @param Organisation $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Organisation $organisation): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        /** @var Collection<Organisation> */
        $collection = $this->filterActionInput($request, Organisation::class);

        $flashMessage = $this->performAction($actionName, $organisation, $collection);

        Session::flash('flash.message', $flashMessage);

        return redirect()
            ->route('my.settings.child-organisations.for.organisation.index', ['organisation' => $organisation->id]);
    }

    /**
     * Execute the given action.
     *
     * @param string                   $action
     * @param Organisation             $organisation
     * @param Collection<Organisation> $organisations
     *
     * @return string
     */
    private function performAction(string $action, Organisation $organisation, Collection $organisations): string
    {
        $flashMessage = '';
        switch ($action) {
            case 'remove_from_organisation':
                $organisations->each(function ($org) {
                    $org->update(['parent_id' => null]);
                });

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
