<?php

namespace App\View\Components\Ui\Collaborate;

use App\View\Components\Ui\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class CrudDataTable extends DataTable
{
    /**
     * @param Request               $request
     * @param Builder|Relation|null $query
     *
     * @return Builder|Relation|null
     */
    public function query(Request $request, Builder|Relation|null $query = null): Builder|Relation|null
    {
        if ($query && $query->hasNamedScope('filter')) {
            // @phpstan-ignore-next-line
            $query->filter($request->all());
        }

        return $query;
    }

    /**
     * @return array<string,mixed>
     */
    public function filters(): array
    {
        return $this->availableFilters;
    }
}
