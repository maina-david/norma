<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\CrudController;
use App\Http\Requests\Customer\TeamRequest;
use App\Models\Customer\Team;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamController extends CrudController
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Team::class;
    }

    protected function appLayout(): string
    {
        return 'layouts.settings';
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'my.settings.teams';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return TeamRequest::class;
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
     * @codeCoverageIgnore
     *
     * @return array<mixed>
     */
    protected static function indexColumns(): array
    {
        return [];
    }

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        switch ($action) {
            case 'edit':
            case 'destroy':
            case 'update':
                $this->authorize('manageInSettings', $arguments);
                break;
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $organisation = $this->activeOrganisationManager->getActive();

        if ($organisation) {
            $baseQuery = $organisation->teams()->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs() ? (new Team())->newQuery() : null;
        }

        if ($request->wantsJson()) {
            // @phpstan-ignore-next-line
            $json = $baseQuery->filter($request->all())
                ->get(['id', 'title'])
                ->toArray();

            return response()->json($json);
        }

        /** @var View $view */
        $view = view('pages.customer.my.team.settings.index', [
            'baseQuery' => $baseQuery,
            'organisation' => $organisation,
        ]);

        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $team): View|Response
    {
        /** @var Team */
        $team = Team::findOrFail($team);
        $this->authorize('manageInSettings', $team);
        /** @var View $view */
        $view = view('pages.customer.my.team.settings.show', ['team' => $team->load('organisation')]);

        return $view;
    }
}
