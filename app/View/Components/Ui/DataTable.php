<?php

namespace App\View\Components\Ui;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\Component;

/**
 * @method Builder|Relation    query(Request $request, Builder|Relation|null $query = null)
 * @method array<string,mixed> fields()
 */
abstract class DataTable extends Component
{
    /**
     * Create a new component instance.
     * NB: Couldn't type hint the baseQuery, as the service container injected
     * a builder object.
     *
     * @param Request                          $request
     * @param bool                             $filterable
     * @param bool                             $sortable
     * @param bool                             $searchable
     * @param string|null                      $currentSort
     * @param string                           $searchSuggestRoute
     * @param string                           $searchSuggestTarget
     * @param bool                             $actionable
     * @param bool                             $headings
     * @param bool                             $focusSearch
     * @param mixed|null                       $baseQuery
     * @param int                              $paginate
     * @param string                           $route
     * @param string                           $actionsRoute
     * @param array<int, string>               $fields
     * @param array<int, string>               $actions
     * @param array<int, string>               $filters
     * @param string                           $filterRoute
     * @param string                           $primaryFilter
     * @param array<int, array<string, mixed>> $primaryFilterOptions Used for the primary filter tabs, format: [['label'=> 'Some Label', 'value' => 1],['label'=> 'Some Heading', 'value' => 2]]
     * @param bool                             $striped
     * @param bool                             $simplePaginate
     * @param bool                             $stickyHeadings
     * @param bool                             $noData
     * @param string|null                      $searchPlaceholder
     * @param LengthAwarePaginator<mixed>|null $paginator            = null,
     * @param array<string, mixed>             $availableFields
     * @param array<string, mixed>             $availableFilters
     * @param array<string, mixed>             $availableActions
     * @param array<string, mixed>             $availableSorts
     * @param bool                             $noFilterScroll
     * @param bool                             $submitToFilter
     * @param bool                             $noTopFilter
     * @param bool                             $sideFilters
     */
    public function __construct(
        protected Request $request,
        public bool $filterable = false,
        public bool $sortable = false,
        public bool $searchable = false,
        public ?string $currentSort = null,
        public string $searchSuggestRoute = '',
        public string $searchSuggestTarget = '',
        public bool $actionable = false,
        public bool $headings = true,
        public bool $focusSearch = false,
        public mixed $baseQuery = null,
        public int $paginate = 25,
        public string $route = '',
        public string $actionsRoute = '',
        public array $fields = [],
        public array $actions = [],
        public array $filters = [],
        public string $filterRoute = '',
        public string $primaryFilter = '',
        public array $primaryFilterOptions = [],
        public bool $striped = true,
        public bool $simplePaginate = false,
        public bool $stickyHeadings = false,
        public bool $noData = false,
        public ?string $searchPlaceholder = null,
        public ?LengthAwarePaginator $paginator = null,
        public array $availableFields = [],
        public array $availableFilters = [],
        public array $availableActions = [],
        public array $availableSorts = [],
        public bool $noFilterScroll = false,
        public bool $submitToFilter = false,
        public bool $noTopFilter = false,
        public bool $sideFilters = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Factory|Closure|string
     */
    public function render()
    {
        return view($this->viewPath(), [
            'rows' => $this->noData ? null : $this->getRows(),
            'selectedFilters' => $this->getFilters(),
            'selectedFields' => $this->getFields(),
            'selectedActions' => $this->getActions(),
        ]);
    }

    /**
     * @return Paginator
     */
    protected function getRows(): Paginator
    {
        if ($this->paginator && !$this->baseQuery) {
            return $this->paginator;
        }

        $query = method_exists($this, 'query')
            ? $this->query($this->request, $this->baseQuery)
            // @codeCoverageIgnoreStart
            : $this->baseQuery;
        // @codeCoverageIgnoreEnd

        $method = $this->simplePaginate ? 'simplePaginate' : 'paginate';

        return $query->{$method}($this->paginate)
            ->appends($this->request->query() ?? []);
    }

    // /**
    //  * Extend this and return the query builder for the data to be fetched.
    //  *
    //  * @param Request               $request
    //  * @param Builder|Relation|null $query
    //  *
    //  * @return Builder|Relation
    //  */
    // abstract public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation;

    /**
     * @return string
     */
    public function viewPath(): string
    {
        return 'components.ui.data-table';
    }

    /**
     * @return array<string,mixed>
     */
    public function filters(): array
    {
        // @codeCoverageIgnoreStart
        return [];
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return array<string,mixed>
     */
    public function actions(): array
    {
        // @codeCoverageIgnoreStart
        return [];
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return array<string,mixed>
     */
    public function getFilters(): array
    {
        $out = [];
        if ($this->request->input('search')) {
            $out['search'] = [
                'label' => __('interface.word_search'),
                'value' => $this->request->input('search'),
            ];
        }
        if (empty($this->filters)) {
            return $out;
        }
        $availableFilters = empty($this->availableFilters) ? $this->filters() : $this->availableFilters;
        foreach ($this->filters as $f) {
            $out[$f] = $availableFilters[$f];
        }

        return $out;
    }

    /**
     * @return array<string,mixed>
     */
    public function getFields(): array
    {
        $out = [];
        if (empty($this->fields)) {
            return $this->fields();
        }
        $availableFields = empty($this->availableFields) ? $this->fields() : $this->availableFields;
        foreach ($this->fields as $f) {
            if (!isset($availableFields[$f])) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $out[$f] = $availableFields[$f];
        }

        return $out;
    }

    /**
     * @return array<string,mixed>
     */
    public function getActions(): array
    {
        $out = [];
        if (empty($this->actions)) {
            return [];
        }
        $availableActions = empty($this->availableActions) ? $this->actions() : $this->availableActions;
        foreach ($this->actions as $a) {
            $out[$a] = $availableActions[$a];
        }

        return $out;
    }

    /**
     * @return string
     */
    public function renderField(string $field, Arrayable $row): string
    {
        $availableFields = empty($this->availableFields) ? $this->fields() : $this->availableFields;

        return $this->renderRenderable($field, $availableFields, $row);
    }

    /**
     * @return string
     */
    public function renderFilter(string $filter, ?Arrayable $data = null): string
    {
        $availableFilters = empty($this->availableFilters) ? $this->filters() : $this->availableFilters;

        return $this->renderRenderable($filter, $availableFilters, $data);
    }

    /**
     * @param string               $renderable
     * @param array<string, mixed> $renderables
     * @param mixed                $data
     *
     * @return string
     */
    private function renderRenderable(
        string $renderable,
        array $renderables,
        mixed $data = null
    ): string {
        $value = $renderables[$renderable] ?? null;
        if (!$value) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }
        $value = call_user_func($value['render'], $data);
        if ($value instanceof View) {
            return $value->render();
        }

        return $value ?? '';
    }

    /**
     * @param string $filterKey
     *
     * @return string|array<string, mixed>
     */
    public function getFilterValue(string $filterKey): string|array
    {
        $multiple = isset($this->filters()[$filterKey]['multiple'])
            ? $this->filters()[$filterKey]['multiple']
            : false;

        return $this->request->input($filterKey, $multiple ? [] : '') ?? '';
    }

    /**
     * @return array<string,mixed>
     */
    public function getActiveFilters(): array
    {
        $out = [];

        foreach ($this->request->only(array_merge(array_keys($this->getFilters()), ['search'])) as $key => $value) {
            if (is_null($value)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * @param string $filter
     * @param mixed  $value
     *
     * @return string
     */
    public function getActiveFilterLabel(string $filter, mixed $value): string
    {
        if ($filter === 'search') {
            // @codeCoverageIgnoreStart
            return $this->request->input('search');
            // @codeCoverageIgnoreEnd
        }

        $filters = $this->getFilters();

        return isset($filters[$filter]['value']) && $filters[$filter]['value'] instanceof Closure
            ? call_user_func($filters[$filter]['value'], $value)
            // @codeCoverageIgnoreStart
            : '';
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string
     */
    public function getFilterRoute(): string
    {
        if ($this->filterRoute !== '') {
            // @codeCoverageIgnoreStart
            return $this->filterRoute;
            // @codeCoverageIgnoreEnd
        }

        return $this->route ?? '';
    }
}
