<?php

namespace App\Http\Controllers\Abstracts;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\AuthorizesCrudActions;
use App\Http\Controllers\Traits\HasCreateAction;
use App\Http\Controllers\Traits\HasDestroyAction;
use App\Http\Controllers\Traits\HasEditAction;
use App\Http\Controllers\Traits\HasResourceActions;
use App\Http\Controllers\Traits\HasShowAction;
use App\Http\Controllers\Traits\HasStoreAction;
use App\Http\Controllers\Traits\HasUpdateAction;
use App\Http\Controllers\Traits\UsesCrudViews;
use App\Support\ResourceNames;
use App\Traits\InteractsWithFilters;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\ComponentAttributeBag;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;

abstract class CrudController extends Controller
{
    use UsesCrudViews;
    use HasUpdateAction;
    use HasStoreAction;
    use HasEditAction;
    use HasCreateAction;
    use HasDestroyAction;
    use HasShowAction;
    use HasResourceActions;
    use AuthorizesCrudActions;
    use InteractsWithFilters;

    /** @var bool */
    protected bool $searchable = true;

    /** @var string */
    protected string $sortBy = 'id';

    /** @var string */
    protected string $sortDirection = 'asc';

    /** @var string */
    protected string $searchResultKey = 'id';

    /** @var array<int, int> */
    private array $searchResultsIds = [];

    /** @var bool */
    protected bool $withCreate = true;

    /** @var bool */
    protected bool $withShow = true;

    /** @var bool */
    protected bool $withUpdate = true;

    /** @var bool */
    protected bool $withDelete = true;

    /**
     * Get the application to be used when authorising the requests.
     *
     * @return ApplicationType
     */
    abstract protected static function application(): ApplicationType;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    abstract protected static function resource(): string;

    /**
     * Get the available sorts.
     *
     * @return array<string, mixed>
     */
    protected static function availableSorts(): array
    {
        return [];
    }

    /**
     * The class that is the main resource that this controller is a relation of.
     *
     * @phpstan-return class-string
     *
     * @return string|null
     */
    protected static function forResource(): ?string
    {
        return null;
    }

    /**
     * @codeCoverageIgnore
     * If the controller is for a pivot relation, what's the name of  the relation.
     *
     * @return string|null
     */
    protected static function pivotRelationName(): ?string
    {
        return null;
    }

    /**
     * @codeCoverageIgnore
     * If the controller is for a pivot relation, what's the name of  the relation.
     *
     * @return string|null
     */
    protected static function pivotClassName(): ?string
    {
        return null;
    }

    /**
     * The class that is the main resource that this controller is a relation of.
     *
     * @phpstan-return class-string
     *
     * @return string|null
     */
    protected function forResourceTitle(): ?string
    {
        return $this->getForResource()->title ?? null;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    abstract protected static function resourceRoute(): string;

    /**
     * Get base resource route parameters that are required to create the route.
     *
     * @return array<string, mixed>
     */
    protected function resourceRouteParams(): array
    {
        return [];
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    abstract protected static function resourceFormRequest(): string;

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<string|int, mixed>
     */
    abstract protected static function indexColumns(): array;

    protected function appLayout(): ?string
    {
        $app = static::application();
        $layout = $app->value === ApplicationType::my()->value ? 'app' : $app->value;

        return 'layouts.' . $layout;
    }

    /**
     * Get the turbo frame target to be updated when crud actions are called requesting a frame update..
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected static function turboTarget(Request $request): ?string
    {
        return null;
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return [];
    }

    /**
     * In case you want to override the default crud views, return the base view path that contains
     * the index and create and edit views.
     *
     * @return string|null
     */
    protected function viewPath(): ?string
    {
        return null;
    }

    /**
     * Get the base search query builder instance.
     *
     * @param Request $request
     *
     * @return \Laravel\Scout\Builder
     */
    protected static function baseSearchQuery(Request $request): \Laravel\Scout\Builder
    {
        $class = static::resource();

        return $class::search($request->get('search'));
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        $class = static::resource();

        /** @var Model */
        $model = (new $class());
        $query = $model->newQuery();

        if ($request->get('search') && method_exists($class, 'search')) {
            $this->searchResultsIds = static::baseSearchQuery($request)->take(2000)->get()
                ->map(function ($item) {
                    /** @var \App\Models\Auth\User $item */
                    return $item->id;
                })
                ->all();

            $query->whereIn($this->searchResultKey, $this->searchResultsIds);
        }

        return $query;
    }

    /**
     * Update the current sorting.
     *
     * @param Request $request
     *
     * @return void
     */
    public function updateSorts(Request $request): void
    {
        /** @var string $sort */
        $sort = $request->query('sort');
        $sorts = static::availableSorts();

        if ($sort && in_array($sort, array_keys($sorts))) {
            $this->sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $this->sortBy = str_replace('-', '', $sort);
        }
    }

    protected function preFetch(Builder $builder): void
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @throws AuthorizationException
     *
     * @return View|Response
     */
    public function index(Request $request): View|Response
    {
        $this->authoriseAction('index');

        $this->updateSorts($request);

        $query = $this->baseQuery($request);

        $query->reorder();
        // @codeCoverageIgnoreStart
        if (empty($this->searchResultsIds)) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        } else {
            $query->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", implode(',', $this->searchResultsIds)));
        }
        // @codeCoverageIgnoreEnd

        $resource = static::resource();

        $columns = $this->getColumns($resource);

        $this->preFetch($query);

        $payload = array_merge(
            [
                'currentSort' => ($this->sortDirection === 'asc' ? '' : '-') . $this->sortBy,
                'availableSorts' => static::availableSorts(),
                'noFilterScroll' => true,
                'application' => static::application(),
                'appLayout' => $this->appLayout(),
                'searchable' => $this->searchable,
                'filters' => $this->resourceFilters(),
                'baseQuery' => $query,
                'baseRoute' => static::resourceRoute(),
                'baseRouteParams' => $this->resourceRouteParams(),
                'withCreate' => $this->withCreate,
                'fields' => $columns,
                'langFile' => ResourceNames::getLangPath(static::resource()),
                'permission' => $this->permissionPrefix(),
                'forResource' => static::forResource() ? $this->forResource() : null,
                'forResourceTitle' => static::forResource() ? $this->forResourceTitle() : null,
                'actionsRoute' => method_exists(static::class, 'resourceActionRoute')
                    ? static::resourceActionRoute()
                    : '',
                'actions' => method_exists(static::class, 'datatableActions')
                    ? static::datatableActions()
                    : [],
            ],
            $this->indexViewData($request),
        );

        $target = $this->turboTarget($request);

        if (!$target || !$request->isForTurboFrame()) {
            return $this->getView('index')->with($payload);
        }

        return singleTurboStreamResponse($target, 'update')
            ->view('pages.crud.index-table', $payload)
            ->toResponse($request);
    }

    /**
     * @param string $resource
     *
     * @throws ReflectionException
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getColumns(string $resource): array
    {
        /** @var Model */
        $model = new $resource();
        $dates = $model->getDates();

        /** @var array<string, array<string, mixed>> */
        $columns = collect(static::indexColumns())->mapWithKeys(function ($item, $key) use ($dates) {
            return $this->updateColumnFields($key, $item, $dates);
        })->toArray();

        if ($this->withShow && Auth::user()?->can($this->permissionPrefix() . '.view')) {
            $first = array_key_first($columns);

            $columns[$first]['render'] = function ($row) use ($first, $columns) {
                $attributes = collect($this->showAnchorAttributes($row))
                    ->map(fn ($val, $key) => sprintf('%s="%s"', $key, $val))
                    ->values()
                    ->join(' ');

                $route = static::resourceRoute() . '.show';

                return sprintf(
                    '<a class="text-primary" href="%s" %s>%s</a>',
                    route($route, array_merge((array) $row->id, $this->resourceRouteParams())),
                    $attributes,
                    call_user_func($columns[$first]['render'], $row)
                );
            };
        }

        if ($this->withUpdate || $this->withDelete) {
            $columns['table_actions'] = [
                'render' => fn ($row) => $this->renderActionButtons($row),
                'heading' => '',
            ];
        }

        return $columns;
    }

    /**
     * Get extra attributest to append to the anchor tag that links to the show page.
     *
     * @param Model $row
     *
     * @return array<string, string>
     */
    protected function showAnchorAttributes(Model $row): array
    {
        return [];
    }

    /**
     * @param mixed $key
     * @param mixed $item
     * @param mixed $dates
     *
     * @return array<mixed, array<mixed, mixed>>
     */
    protected function updateColumnFields(mixed $key, mixed $item, mixed $dates): array
    {
        $key = is_numeric($key) ? $item : $key;

        if (is_string($item)) {
            return [
                $item => [
                    'heading' => __(ResourceNames::getLangPath(static::resource()) . ".{$item}"),
                    'render' => in_array($key, $dates)
                        ? fn ($row) => static::renderTimestamp($row->{$item}?->timestamp)
                        : fn ($row) => $row->{$item},
                ],
            ];
        }

        if (!is_array($item)) {
            return [
                $key => [
                    'render' => $item,
                    'heading' => __(ResourceNames::getLangPath(static::resource()) . ".{$key}"),
                ],
            ];
        }

        // @codeCoverageIgnoreStart
        return [$key => $item];
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get extra data to be sent back to the index view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function indexViewData(Request $request): array
    {
        return [];
    }

    /**
     * Render the partial with the row data.
     *
     * @param Model                $row
     * @param string               $view
     * @param array<string, mixed> $data
     *
     * @return string
     */
    protected static function renderPartial(Model $row, string $view, array $data = []): string
    {
        $resource = ResourceNames::getViewPath(static::resource());

        return view("pages.{$resource}.partials.{$view}", array_merge($data, ['row' => $row]))->render();
    }

    /**
     * Render the timestamp component.
     *
     * @param int|null $value
     *
     * @return string
     */
    protected static function renderTimestamp(?int $value): string
    {
        return view('components.ui.timestamp', [
            'attributes' => new ComponentAttributeBag(),
            'timestamp' => $value,
        ])->render();
    }

    /**
     * @param string $href
     * @param string $text
     * @param bool   $turbo
     *
     * @return string
     */
    protected static function renderLink(string $href, string $text, bool $turbo = true): string
    {
        return view('partials.ui.link', [
            'href' => $href,
            'linkText' => $text,
            'turbo' => $turbo,
        ])->render();
    }

    /**
     * Render the timestamp component.
     *
     * @param Model $row
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function renderActionButtons(Model $row): string
    {
        return view('components.ui.table-action-buttons', [
            'attributes' => new ComponentAttributeBag(),
            'withUpdate' => $this->withUpdate,
            'withDelete' => $this->withDelete,
            'basePermission' => $this->permissionPrefix(),
            'baseRoute' => static::resourceRoute(),
            'baseRouteParams' => $this->resourceRouteParams(),
            'resourceId' => $row->getAttribute('id'),
        ])->render();
    }

    /**
     * @return Model
     */
    protected function getForResource(): Model
    {
        /** @var Route $route */
        $route = app(Request::class)->route();

        $id = array_values($route->parameters())[0] ?? null;
        /** @var Model */
        $resource = static::forResource();

        /** @var Model */
        return $resource::findOrFail($id);
    }

    /**
     * Returns the resource ID. for resources that have a parent resource, it's the second route parameter rather than the first.
     *
     * @return int|null
     */
    protected function getResourceId(): ?int
    {
        $parameterNumber = static::forResource() ? 1 : 0;
        /** @var Route $route */
        $route = app(Request::class)->route();

        $id = array_values($route->parameters())[$parameterNumber] ?? null;

        return $id ? (int) $id : null;
    }
}
