<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Actions\Customer\Norma\Activate;
use App\Actions\Customer\Norma\CloneNorma;
use App\Actions\Customer\Norma\Deactivate;
use App\Actions\Customer\Norma\UpdateModule;
use App\Enums\Application\ApplicationType;
use App\Enums\Ontology\CategoryType;
use App\Http\Controllers\Abstracts\CrudController;
use App\Http\Controllers\Traits\PerformsActions;
use App\Http\Requests\Customer\NormaCompilationSettingRequest;
use App\Http\Requests\Customer\NormaModulesRequest;
use App\Http\Requests\Customer\NormaRequest;
use App\Models\Auth\User;
use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Services\Customer\ActiveOrganisationManager;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class NormaController extends CrudController
{
    use PerformsActions;

    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
    ) {
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Norma::class;
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
        return 'my.settings.normas';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return NormaRequest::class;
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
     * @param Request $request
     *
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $organisation = $this->activeOrganisationManager->getActive();

        if ($organisation) {
            $baseQuery = $organisation->normas()->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs() ? (new Norma())->newQuery()->with(['organisation', 'organisation.partner']) : null;
        }

        if ($request->wantsJson()) {
            // @phpstan-ignore-next-line
            $json = $baseQuery->without(['organisation', 'organisation.partner'])->filter($request->all())
                ->get(['id', 'title'])
                ->toArray();

            return response()->json($json);
        }

        /** @var View $view */
        $view = view('pages.customer.my.norma.settings.index', [
            'baseQuery' => $baseQuery,
            'organisation' => $organisation,
        ]);

        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $norma): View|Response
    {
        /** @var Norma */
        $norma = Norma::findOrFail($norma);
        $this->authorize('manageInSettings', $norma);
        /** @var View $view */
        $view = view('pages.customer.my.norma.settings.show', [
            'norma' => $norma->load('location'),
            'activeTab' => $request->input('t', null),
        ]);

        return $view;
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
        /** @var Collection<Norma> */
        $normas = $this->filterActionInputForOrg($request, Norma::class, $organisation);

        $flashMessage = $this->performAction($actionName, $normas);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.normas.index'));

        return $response;
    }

    /**
     * @param string             $action
     * @param Collection<Norma> $normas
     *
     * @return string
     */
    private function performAction(string $action, Collection $normas): string
    {
        switch ($action) {
            case 'deactivate':
                Deactivate::run($normas);
                break;
            case 'activate':
                Activate::run($normas);
                break;
                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }
        /** @var string $flashMessage */
        $flashMessage = __('actions.success');

        return $flashMessage;
    }

    public function updateModules(NormaModulesRequest $request, Norma $norma): RedirectResponse
    {
        foreach ($request->validated() as $module => $value) {
            UpdateModule::run($norma, $module, $value);
        }

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.normas.modules.index', ['norma' => $norma->id]));

        return $response;
    }

    public function modules(ActiveOrganisationManager $activeOrganisationManager, Norma $norma): View
    {
        $defaultModules = config('norma.model_settings.App\Models\Customer\Norma.defaults.modules');

        /** @var View */
        return view('pages.customer.my.norma.settings.modules', [
            'modules' => array_merge($defaultModules, $norma->settings['modules']),
            'norma' => $norma,
            'organisation' => $activeOrganisationManager->getActive(),
        ]);
    }

    public function updateCompilationSettings(NormaCompilationSettingRequest $request, Norma $norma): RedirectResponse
    {
        /** @var CompilationSetting */
        $settings = CompilationSetting::findOrFail($norma->id);
        $settings->update($request->validated());

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.normas.compilation-settings.index', ['norma' => $norma->id]));

        return $response;
    }

    public function compilationSettings(ActiveOrganisationManager $activeOrganisationManager, Norma $norma): View
    {
        /** @var View */
        return view('pages.customer.my.norma.settings.compilation-settings', [
            'resource' => $norma->compilationSetting,
            'norma' => $norma,
            'organisation' => $activeOrganisationManager->getActive(),
        ]);
    }

    /**
     * Generates labels.
     *
     * @param Collection<Category> $children
     * @param Category             $item
     *
     * @return string
     */
    protected function generateLabel(Collection $children, Category $item): string
    {
        if (!$item->parent_id || !$parent = $children->where('id', $item->parent_id)->first()) {
            return $item->display_label;
        }

        /** @var Category $parent */
        $parentLabel = $this->generateLabel($children, $parent);

        return "{$parentLabel} | {$item->display_label}";
    }

    /**
     * Get the economic topics.
     *
     * @return array<string, mixed>
     */
    protected function getEconomicTopics(): array
    {
        /** @var string $key */
        $key = config('cache-keys.ontology.category.type-economic.key');
        /** @var int $expiry */
        $expiry = config('cache-keys.ontology.category.type-economic.expiry');

        /** var array<string, mixed> */
        return Cache::remember($key, now()->addMinutes($expiry), function () {
            /** @var Collection<Category> $categories */
            $categories = Category::where('category_type_id', CategoryType::ECONOMIC->value)
                ->with(['descriptions' => fn ($query) => $query->whereNull('location_id')])
                ->where('level', 2)
                ->with([
                    'descendantsUnordered' => function ($builder) {
                        $builder->select([(new Category())->qualifyColumn('id'), 'parent_id', 'display_label'])
                            ->where('level', 3)
                            ->with(['descriptions' => fn ($query) => $query->whereNull('location_id')]);
                    },
                ])
                ->get(['id', 'parent_id', 'display_label']);

            $types = ['' => ''];
            $descriptions = [];

            foreach ($categories as $category) {
                // Removed since they don't want to see the top level category.
                $types[$category->id] = $category->display_label;
                $category->descendantsUnordered->push($category);
                $description = $category->descriptions->first();

                if ($description) {
                    $descriptions[$category->id] = $description->description;
                }

                foreach ($category->descendantsUnordered as $child) {
                    $types[$child->id] = $this->generateLabel($category->descendantsUnordered, $child);
                    $description = $child->descriptions->first();

                    if ($description) {
                        $descriptions[$child->id] = $description->description;
                    }
                }
            }

            return [
                'categories' => collect($types)->sort()->all(),
                'categoryDescriptions' => $descriptions,
            ];
        });
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            ...$this->getEconomicTopics(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return $this->createViewData($request);
    }

    /**
     * Clone the given norma.
     *
     * @param Norma $norma
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function clone(Norma $norma): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->isMySuperUser(), 404);

        $clone = app(CloneNorma::class)->handle($norma);

        return redirect()->route('my.settings.normas.edit', ['norma' => $clone->id]);
    }
}
