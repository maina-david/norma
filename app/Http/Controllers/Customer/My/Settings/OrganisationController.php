<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Actions\Customer\Organisation\UpdateModule;
use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\CrudController;
use App\Http\Requests\Customer\OrganisationModulesRequest;
use App\Http\Requests\Customer\OrganisationRequest;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class OrganisationController extends CrudController
{
    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(protected ActiveOrganisationManager $activeOrganisationManager)
    {
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Organisation::class;
    }

    protected function appLayout(): string
    {
        return 'layouts.settings';
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<mixed>
     */
    protected static function indexColumns(): array
    {
        return [];
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'my.settings.organisations';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return OrganisationRequest::class;
    }

    /**
     * Get the application to be used when authorising the requests.
     *
     * @codeCoverageIgnore
     *
     * @return ApplicationType
     */
    protected static function application(): ApplicationType
    {
        return ApplicationType::my();
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $baseQuery = (new Organisation())->newQuery()->with('partner');

        if ($request->wantsJson()) {
            return $this->indexJson($request);
        }

        /** @var View $view */
        $view = view('pages.customer.my.organisation.settings.index', ['baseQuery' => $baseQuery]);

        return $view;
    }

    /**
     * Get the organisation search results.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexJson(Request $request): JsonResponse
    {
        $baseQuery = (new Organisation())->newQuery()->with('partner');

        $json = $baseQuery->without('partner')
            ->filter($request->all())
            ->get(['id', 'title'])
            ->toArray();

        return response()->json($json);
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $organisation): View|Response
    {
        /** @var Organisation */
        $organisation = Organisation::findOrFail($organisation);
        /** @var View $view */
        $view = view('pages.customer.my.organisation.settings.show', [
            'organisation' => $organisation,
            'activeTab' => $request->input('t', null),
        ]);

        return $view;
    }

    /**
     * @param Organisation $organisation
     *
     * @return RedirectResponse
     */
    public function activate(Organisation $organisation): RedirectResponse
    {
        $this->activeOrganisationManager->activate($organisation->id);

        return back();
    }

    /**
     * @return RedirectResponse
     */
    public function activateAll(): RedirectResponse
    {
        if (!userCanManageAllOrgs()) {
            // @codeCoverageIgnoreStart
            abort(403);
            // @codeCoverageIgnoreEnd
        }
        $this->activeOrganisationManager->activateAll();

        return back();
    }

    /**
     * @param OrganisationModulesRequest $request
     * @param Organisation               $organisation
     *
     * @return RedirectResponse
     */
    public function updateModules(OrganisationModulesRequest $request, Organisation $organisation): RedirectResponse
    {
        foreach ($request->validated() as $module => $value) {
            UpdateModule::run($organisation, $module, $value);
        }

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.organisations.modules.index', ['organisation' => $organisation->id]));

        return $response;
    }

    /**
     * @param Organisation $organisation
     *
     * @return Response
     */
    public function modules(Organisation $organisation): Response
    {
        $defaultModules = config('norma.model_settings.App\Models\Customer\Organisation.defaults.modules');

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.customer.my.organisation.modules-form',
            'target' => 'settings-modules-for-organisation-' . $organisation->id,
            'modules' => array_merge($defaultModules, $organisation->settings['modules']),
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }
}
