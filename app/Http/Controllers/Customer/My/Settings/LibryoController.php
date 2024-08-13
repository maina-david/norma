<?php

namespace App\Http\Controllers\Customer\My\Settings;

use App\Actions\Customer\Libryo\Activate;
use App\Actions\Customer\Libryo\CloneLibryo;
use App\Actions\Customer\Libryo\Deactivate;
use App\Actions\Customer\Libryo\UpdateModule;
use App\Enums\Application\ApplicationType;
use App\Enums\Ontology\CategoryType;
use App\Http\Controllers\Abstracts\CrudController;
use App\Http\Controllers\Traits\PerformsActions;
use App\Http\Requests\Customer\LibryoCompilationSettingRequest;
use App\Http\Requests\Customer\LibryoModulesRequest;
use App\Http\Requests\Customer\LibryoRequest;
use App\Models\Auth\User;
use App\Models\Customer\CompilationSetting;
use App\Models\Customer\Libryo;
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

class LibryoController extends CrudController
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
        return Libryo::class;
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
        return 'my.settings.libryos';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return LibryoRequest::class;
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
            $baseQuery = $organisation->libryos()->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs() ? (new Libryo())->newQuery()->with(['organisation', 'organisation.partner']) : null;
        }

        if ($request->wantsJson()) {
            // @phpstan-ignore-next-line
            $json = $baseQuery->without(['organisation', 'organisation.partner'])->filter($request->all())
                ->get(['id', 'title'])
                ->toArray();

            return response()->json($json);
        }

        /** @var View $view */
        $view = view('pages.customer.my.libryo.settings.index', [
            'baseQuery' => $baseQuery,
            'organisation' => $organisation,
        ]);

        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $libryo): View|Response
    {
        /** @var Libryo */
        $libryo = Libryo::findOrFail($libryo);
        $this->authorize('manageInSettings', $libryo);
        /** @var View $view */
        $view = view('pages.customer.my.libryo.settings.show', [
            'libryo' => $libryo->load('location'),
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
        /** @var Collection<Libryo> */
        $libryos = $this->filterActionInputForOrg($request, Libryo::class, $organisation);

        $flashMessage = $this->performAction($actionName, $libryos);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.libryos.index'));

        return $response;
    }

    /**
     * @param string             $action
     * @param Collection<Libryo> $libryos
     *
     * @return string
     */
    private function performAction(string $action, Collection $libryos): string
    {
        switch ($action) {
            case 'deactivate':
                Deactivate::run($libryos);
                break;
            case 'activate':
                Activate::run($libryos);
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

    public function updateModules(LibryoModulesRequest $request, Libryo $libryo): RedirectResponse
    {
        foreach ($request->validated() as $module => $value) {
            UpdateModule::run($libryo, $module, $value);
        }

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.libryos.modules.index', ['libryo' => $libryo->id]));

        return $response;
    }

    public function modules(ActiveOrganisationManager $activeOrganisationManager, Libryo $libryo): View
    {
        $defaultModules = config('libryo.model_settings.App\Models\Customer\Libryo.defaults.modules');

        /** @var View */
        return view('pages.customer.my.libryo.settings.modules', [
            'modules' => array_merge($defaultModules, $libryo->settings['modules']),
            'libryo' => $libryo,
            'organisation' => $activeOrganisationManager->getActive(),
        ]);
    }

    public function updateCompilationSettings(LibryoCompilationSettingRequest $request, Libryo $libryo): RedirectResponse
    {
        /** @var CompilationSetting */
        $settings = CompilationSetting::findOrFail($libryo->id);
        $settings->update($request->validated());

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.libryos.compilation-settings.index', ['libryo' => $libryo->id]));

        return $response;
    }

    public function compilationSettings(ActiveOrganisationManager $activeOrganisationManager, Libryo $libryo): View
    {
        /** @var View */
        return view('pages.customer.my.libryo.settings.compilation-settings', [
            'resource' => $libryo->compilationSetting,
            'libryo' => $libryo,
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
     * Clone the given libryo.
     *
     * @param Libryo $libryo
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function clone(Libryo $libryo): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user->isMySuperUser(), 404);

        $clone = app(CloneLibryo::class)->handle($libryo);

        return redirect()->route('my.settings.libryos.edit', ['libryo' => $clone->id]);
    }
}
