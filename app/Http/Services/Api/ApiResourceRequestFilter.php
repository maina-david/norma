<?php

namespace App\Http\Services\Api;

use App\Http\Exceptions\InvalidUriException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * An HTTP layer service to.
 */
class ApiResourceRequestFilter
{
    /**
     * @param Request $request
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * Returns a list of fields to show for the output.
     *
     * @return array<string>
     **/
    public function getFields()
    {
        $fields = $this->request->input('fields', ['*']);

        return is_string($fields) ? explode(',', $fields) : $fields;
    }

    /**
     * Gets the page from the query string.
     *
     * @return int|null
     **/
    public function getPage(): ?int
    {
        $page = $this->request->input('page', null);

        return $page ? (int) $page : null;
    }

    /**
     * Gets the per page count from the query string.
     *
     * @return int|null
     **/
    public function getPerPage(): ?int
    {
        $perPage = $this->request->input('perPage', null);

        return $perPage ? (int) $perPage : null;
    }

    /**
     * Returns the filters formatted to be passed to the query.
     *
     * @return array<int, array<mixed>>
     **/
    public function getFilters(): array
    {
        $filters = $this->request->input('filters', []);

        $filtersOut = [];
        $count = 0;

        foreach ($filters as $filter) {
            $multiFilters = explode('||', $filter);

            if (count($multiFilters) > 1) {
                $filtersOut[$count] = [];
                foreach ($multiFilters as $multiFilter) {
                    $val = strtolower($multiFilter) !== 'or' ? $this->stripFilter($multiFilter) : 'or';
                    array_push($filtersOut[$count], $val);
                }
            } else {
                $filtersOut[$count] = $this->stripFilter($multiFilters[0]);
            }
            $count++;
        }

        return $filtersOut;
    }

    /**
     * Returns the filters formatted to be passed to the query.
     *
     * @param string $filter
     *
     * @throws InvalidUriException
     *
     * @return array<mixed>
     **/
    protected function stripFilter(string $filter): array
    {
        $filter = explode(',', $filter);

        /** @var string */
        $errorMessage = __('exceptions.api.invalid_uri.filter');
        if (!is_array($filter) || count($filter) < 3) {
            throw new InvalidUriException(SymfonyResponse::HTTP_BAD_REQUEST, $errorMessage);
        }

        [$field, $operator, $value] = array_values($filter);
        /** @var string */
        $errorMessage = __('exceptions.api.invalid_uri.filter_operand');
        if ($operator !== '=' && !isset(config('api.filter_operators')[$operator])) {
            throw new InvalidUriException(SymfonyResponse::HTTP_BAD_REQUEST, $errorMessage);
        }
        if ($operator === '=' && $value !== 'null') {
            throw new InvalidUriException(SymfonyResponse::HTTP_BAD_REQUEST, $errorMessage);
        }
        $operator = $operator === '='
            ? '='
            : config('api.filter_operators')[$operator];

        $boolean = $filter[3] ?? 'and';
        $out = [$field, $operator, $value, $boolean];

        return $out;
    }

    /**
     * Returns the order as required by the query builder.
     *
     * @return array<int, array<int, string>>
     **/
    public function getOrder(): array
    {
        $order = $this->request->input('sort', '');

        if ($order == '') {
            return [];
        }

        $orderOut = [];

        $order = explode(',', $order);

        foreach ($order as $field) {
            if (str_starts_with($field, '-')) {
                $direction = 'desc';
                $field = substr($field, 1);
            } else {
                $direction = 'asc';
            }

            $orderOut[] = [
                $field,
                $direction,
            ];
        }

        return $orderOut;
    }

    /**
     * Returns the counts formatted to be passed to the query.
     *
     * @return array<int, string>
     **/
    public function getCounts(): array
    {
        if (!$this->request->has('count')) {
            return [];
        }

        $counts = $this->request->input('count');

        return is_string($counts) ? explode(',', $counts) : $counts;
    }

    /**
     * Returns the relationship constraints formatted to be passed to the query.
     * e.g. 'registers|id,In,[1,2,3]'.
     *
     * @return array<string>
     **/
    public function getHas(): array
    {
        $has = $this->request->input('has', []);

        return is_array($has) ? $has : [];
    }

    /**
     * @return array<int, string>
     **/
    public function getIncludes(): array
    {
        $separator = app(Request::class)->routeIs('*.v1.*') ? ',' : '|';

        return explode($separator, $this->request->input('include', ''));
    }

    /**
     * Gets the ids from the query.
     *
     * @return array<int>
     **/
    public function getIds(): array
    {
        $ids = $this->request->input('ids', ['*']);

        return is_string($ids) ? explode(',', $ids) : $ids;
    }
}
