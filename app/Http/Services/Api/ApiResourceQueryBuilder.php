<?php

namespace App\Http\Services\Api;

use App\Http\Requests\ApiMultiGetRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use InvalidArgumentException;

/**
 * An HTTP layer service to build an eloquent database query builder object from a GET request
 * for a collection of items.
 */
class ApiResourceQueryBuilder
{
    /**
     * Builds a filtered query based on query parameters
     * Typehinting with ApiMultiGetRequest for static analysis to work on Request macros.
     *
     * @param Builder            $query
     * @param ApiMultiGetRequest $request
     *
     * @return Builder
     */
    public function buildFilteredQuery(Builder $query, Request $request)
    {
        /** @var array<int, int|string> $ids */
        $ids = $request->getIdsFromQueryParams();
        $order = $request->getOrderFromQueryParams();

        if (Arr::first($ids) !== '*') {
            $query->whereKey($ids);
        }

        $this->applyFilters($query, $request->getFiltersFromQueryParams());
        $this->applyHasFilters($query, $request->getHasFromQueryParams());

        if (!empty($order)) {
            foreach ($order as $field) {
                $query->orderBy($field[0], $field[1]);
            }
        }

        return $query;
    }

    /**
     * Applies the given filters to the query. The filters needs to be an array of arrays, which have the first
     * value as the field name, the second value as the operator, and the third as the value to match.
     *
     * @param Builder             $query
     * @param array<array<mixed>> $filters
     *
     * @return void
     *
     **/
    protected function applyFilters(Builder $query, array $filters = []): void
    {
        if (empty($filters)) {
            return;
        }

        $query->where(function ($queryBuilder) use ($filters) {
            foreach ($filters as $filter) {
                if (is_array($filter[0])) {
                    $constraint = 'where';
                    if (Arr::last($filter) === 'or') {
                        $constraint = 'orWhere';
                        array_pop($filter);
                    }

                    $queryBuilder->{$constraint}(function ($q) use ($filter) {
                        foreach ($filter as $subFilter) {
                            $this->applyMultiFilters($q, $subFilter);
                        }
                    });
                } else {
                    $queryBuilder->where(function ($q) use ($filter) {
                        $this->applyMultiFilters($q, $filter);
                    });
                }
            }
        });
    }

    /**
     *  Grouped filters can be applied like so:
     *  filters[]=fname,like,'%john%'||sname,like,'%john%',or||email,like,'%john%'||or
     *  the or at the end determines whether the group is a `where` or an `orWhere` query
     *  if left blank, it's a `where`.
     *
     * @param Builder      $query
     * @param array<mixed> $filter
     *
     * @return void
     *
     **/
    protected function applyMultiFilters(Builder $query, array $filter): void
    {
        if (is_array($filter[0])) {
            // think this is dead code... let's just ignore coverage
            // @codeCoverageIgnoreStart
            $boolean = !is_array(last($filter)) && strtolower(last($filter)) === 'or' ? 'or' : 'and';
            $query->where(function ($q) use ($filter, $boolean) {
                foreach ($filter as $c => $f) {
                    if ($c === (count($filter) - 1) && $boolean === 'or') {
                        continue;
                    }
                    $this->applyQueryOperands($q, $filter);
                }
            }, null, null, $boolean);
        // @codeCoverageIgnoreEnd
        } else {
            $this->applyQueryOperands($query, $filter);
        }
    }

    /**
     * Apply the query filters to the query matching the operands used.
     *
     * @param Builder      $query
     * @param array<mixed> $filter
     *
     * @return Builder
     */
    protected function applyQueryOperands(Builder $query, array $filter = [])
    {
        if (!is_array($filter)) {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException('Filter needs to consist of arrays');
            // @codeCoverageIgnoreEnd
        }
        if (count($filter) < 3) {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException('Filter needs to have three values. field name, operator and value');
            // @codeCoverageIgnoreEnd
        }

        $boolean = isset($filter[3]) && $filter[3] == 'or' ? 'or' : 'and';

        if ($filter[2] === 'null' && $filter[1] === '=') {
            $query->whereNull($filter[0], $boolean);
        } elseif ($filter[1] === 'In') {
            $query->whereIn($filter[0], explode('|', $filter[2]), $boolean);
        } elseif ($filter[1] === 'between') {
            $query->whereBetween($filter[0], explode('|', $filter[2]), $boolean);
        } else {
            $query->where($filter[0], $filter[1], $filter[2], $boolean);
        }

        return $query;
    }

    /**
     * Apply the Has query filter.
     *
     * @param Builder       $query
     * @param array<string> $filters
     */
    protected function applyHasFilters(Builder $query, array $filters = []): void
    {
        if (empty($filters)) {
            return;
        }

        $query->where(function ($queryBuilder) use ($filters) {
            foreach ($filters as $filter) {
                $multiHas = explode('||', $filter);

                if (count($multiHas) > 1) {
                    $this->mapMultiHas($queryBuilder, $multiHas);

                    continue;
                }

                $this->mapNormalHas($queryBuilder, $filter);
            }
        });
    }

    /**
     * Apply a multi-has or query. Use of two pipes on the same filter denote OR.
     * e.g. registers|id,In,[1,2,3]||registers|id,eq,4.
     *
     * @param Builder      $query
     * @param array<mixed> $filters
     *
     * @return void
     */
    protected function mapMultiHas(Builder $query, array $filters = []): void
    {
        $query->where(function ($q) use ($filters) {
            foreach ($filters as $filter) {
                $this->mapNormalHas($q, $filter, true);
            }
        });
    }

    /**
     * Prepare the has query and map it to whereHas or basic has.
     *
     * @param Builder $query
     * @param string  $filter
     * @param bool    $orWhere
     *
     * @return void
     */
    protected function mapNormalHas(Builder $query, string $filter, bool $orWhere = false): void
    {
        $parsedFilter = explode('|', $filter);
        $parsedCount = count($parsedFilter);

        if ($parsedCount === 1) {
            $this->prepareHas($query, $parsedFilter[0], $orWhere);

            return;
        }

        if ($parsedCount === 2) {
            $this->prepareWhereHas($query, $parsedFilter, $orWhere);
        }
    }

    /**
     * Prepare the has query filter.
     *
     * @param Builder $query
     * @param string  $relation
     * @param bool    $orWhere
     *
     * @return Builder
     */
    protected function prepareHas(Builder $query, string $relation, bool $orWhere = false)
    {
        if ($orWhere) {
            return $query->orHas($relation);
        }

        return $query->has($relation);
    }

    /**
     * Apply the whereHas query filter.
     *
     * @param Builder       $query
     * @param array<string> $filters
     * @param bool          $orWhere
     *
     * @return Builder
     */
    protected function prepareWhereHas(Builder $query, array $filters = [], bool $orWhere = false)
    {
        $relation = $filters[0];
        /** @var string */
        $constraints = preg_replace('/[\[\]]/', '', $filters[1]);
        $constraints = explode(',', $constraints);
        $constraints = $this->mapConstraints(...$constraints);

        if (!$orWhere) {
            return $query
                ->whereHas($relation, function ($builder) use ($constraints) {
                    $this->applyQueryOperands($builder, $constraints);
                });
        }

        return $query
            ->orWhereHas($relation, function ($builder) use ($constraints) {
                $this->applyQueryOperands($builder, $constraints);
            });
    }

    /**
     * Map the constraints from the client. e.g. 'registers|id,In,[1,2,3]'.
     *
     * @param string        $field
     * @param string        $operand
     * @param array<string> ...$values
     *
     * @return array<mixed>
     */
    protected function mapConstraints(string $field, string $operand, ...$values): array
    {
        if (count($values) === 1) {
            $values = $values[0];
        }

        if (is_array($values)) {
            // @phpstan-ignore-next-line
            $values = implode('|', $values);
        }

        return [$field, $operand, $values];
    }
}
